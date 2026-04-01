<?php
namespace OpenpayStores\Services;

use OpenpayStores\Includes\OpenpayStoresErrorHandler;

// Ensure WordPress functions are available
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class OpenpayChargeService
{

    private $logger;
    private $order;
    private $customer_service;
    private $openpay;
    private $transaction_id;

    public function __construct($openpay, $order, $customer_service)
    {
        $this->logger = wc_get_logger();
        $this->order = $order;
        $this->customer_service = $customer_service;
        $this->openpay = $openpay;
    }

    public function get_transaction_id()
    {
        return $this->transaction_id;
    }

    public function processOpenpayCharge($payment_settings)
    {
        $this->logger->info('[OpenpayChargeService.processOpenpayCharge] Inicio');
        $charge_request = $this->collectChargeData($payment_settings);
        $this->logger->info('[OpenpayChargeService.processOpenpayCharge] charge_request - ' . json_encode($charge_request));
        $charge = $this->create($payment_settings['openpay_customer'], $charge_request);
        $this->logger->info('[OpenpayChargeService.processOpenpayCharge] charge - ' . json_encode($charge));

        if ($charge != false) {
            $this->transaction_id = $charge->id;
            $reference = $charge->payment_method->reference;
            $barcode_url = $charge->payment_method->barcode_url;
            $due_date = $charge->due_date;
            $this->logger->info('URL PDF:' . $payment_settings['pdf_url_base']);
            $pdf_url = $payment_settings['pdf_url_base'] . '/' . $payment_settings['merchant_id'] . "/" . 'transaction' . "/" . $charge->id;
            //WC()->session->set('pdf_url', $pdf_url);
            //Save data for the ORDER
            if ($payment_settings["sandbox"]) {
                $customer_id = get_user_meta(get_current_user_id(), '_openpay_customer_sandbox_id', true);
            } else {
                $customer_id = get_user_meta(get_current_user_id(), '_openpay_customer_id', true);
            }

            $this->saveChargeMetaData($charge, $payment_settings['country'], $pdf_url, $barcode_url, $reference, $due_date);
            $this->logger->info('[OpenpayChargeService.processOpenpayCharge] Fin');
            return $charge;
        } else {
            return false;
        }
    }

    public function create($openpay_customer, $charge_request)
    {
        $order_id = $this->order->get_id();
        if (is_user_logged_in()) {
            $customer_id = $this->order->get_customer_id();
            $charge = OpenpayStoresErrorHandler::catchOpenpayStoreError(function () use ($openpay_customer, $charge_request, $order_id, $customer_id) {
                return $openpay_customer->charges->create($charge_request);
            }, $order_id, $customer_id);
            return $charge;
        } else {
            $this->logger->info('[OpenpayChargeService.create ( Customer NOT LoggedIn )] Inicio');
            $openpay = $this->openpay;
            $charge = OpenpayStoresErrorHandler::catchOpenpayStoreError(function () use ($openpay, $charge_request, $order_id) {
                return $openpay->charges->create($charge_request);
            }, $order_id);
            $this->logger->info('[OpenpayChargeService.create ( Customer NOT LoggedIn )] Fin');
            return $charge;
        }
    }

    private function collectChargeData($payment_settings)
    {
        date_default_timezone_set('America/Mexico_City');
        $this->logger->info('collectChargeData DATE - ' . date('d/m/Y == H:i:s'));
        $due_date = date('Y-m-d\TH:i:s', strtotime('+ ' . $payment_settings['deadline'] . ' hours'));

        $charge_request = array(
            "method" => "store",
            "amount" => number_format((float) $this->order->get_total(), 2, '.', ''),
            "currency" => strtolower(get_woocommerce_currency()),
            "description" => sprintf("Cargo para %s", $this->order->get_billing_email()),
            "order_id" => $this->order->get_id(),
            'due_date' => $due_date,
            "origin_channel" => "PLUGIN_WOOCOMMERCE"
        );

        if (!is_user_logged_in()) {
            $charge_request["customer"] = $this->customer_service->collectCustomerData($this->order);
        }

        if ($payment_settings["country"] === 'CO') {
            $charge_request['iva'] = $payment_settings["iva"];
        }

        return $charge_request;
    }

    /**
     * @param $charge
     * @param $country
     * @param string $pdf_url
     * @param $barcode_url
     * @param $reference
     * @param $due_date
     * @return void
     */
    public function saveChargeMetaData($charge, $country, string $pdf_url, $barcode_url, $reference, $due_date): void
    {
        $this->order->update_meta_data('_transaction_id', $charge->id);
        $this->order->update_meta_data('_country', $country);
        $this->order->update_meta_data('_pdf_url', $pdf_url);
        $this->order->update_meta_data('_openpay_barcode_url', $barcode_url);
        $this->order->update_meta_data('_openpay_reference', $reference);
        $this->order->update_meta_data('_due_date', $due_date);
    }

}