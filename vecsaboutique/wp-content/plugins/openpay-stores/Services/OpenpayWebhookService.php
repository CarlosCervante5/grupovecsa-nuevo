<?php

namespace OpenpayStores\Services;

use Openpay\Data\OpenpayApi;
use Openpay\Resources\OpenpayWebhook;
use WC_Logger;
use OpenpayStores\Includes\OpenpayStoresErrorHandler;

class OpenpayWebhookService
{
    private $openpay;
    private $logger;

    /**
     * El constructor recibe la instancia del SDK de Openpay (Inyección de Dependencias).
     */
    public function __construct(OpenpayApi $openpay, WC_Logger $logger)
    {
        $this->openpay = $openpay;
        $this->logger = $logger;
    }

    /**
     * Busca un webhook existente por su URL.
     * @return \OpenpayWebhook|null El objeto webhook si se encuentra, o null si no.
     */
    public function findWebhookByUrl(string $url)
    {
        $webhooks = $this->openpay->webhooks->getList([]);
        foreach ($webhooks as $webhook) {
            if ($webhook->url === $url) {
                return $webhook;
            }
        }
        return null;
    }

    /**
     * Obtiene la lista completa de webhooks registrados en Openpay.
     *
     * @return array
     */
    public function getAllWebhooks(): array
    {
        return $this->openpay->webhooks->getList(['limit' => 20]);
    }

    /**
     * Crea un nuevo webhook en la API de Openpay.
     * @return OpenpayWebhook
     * @throws \Exception Si la creación falla.
     */
    public function createWebhook(string $url): OpenpayWebhook
    {
        $webhook_data = [
            'url' => $url,
            'event_types' => [
                'verification',
                'charge.succeeded',
                'charge.created',
                'charge.cancelled',
                'transaction.expired'
            ]
        ];

        $openpay = $this->openpay;
        $webhook = OpenpayStoresErrorHandler::catchOpenpayStoreError(function () use($openpay, $webhook_data) {
            return $openpay->webhooks->add($webhook_data);
         });
        return $webhook;
    }

    /**
     * Elimina un webhook por su ID.
     *
     * @param string $id El ID del webhook a eliminar.
     * @return void
     * @throws \Exception Si la eliminación falla.
     */
    public function deleteWebhook(string $id)
    {
        try {
            $webhook = $this->openpay->webhooks->get($id);
            if ($webhook) {
                $webhook->delete();
            }
        } catch (\Exception $e) {
            throw new \Exception('Error al intentar eliminar el webhook ' . $id . ': ' . $e->getMessage());
        }
    }

    /**
     * Reconcilia los webhooks: busca el activo, crea si no existe, y borra obsoletos.
     *
     * @param string $target_url_pretty La URL "bonita" objetivo (ej: .../wc-api/Openpay_Stores)
     * @param string $target_url_simple La URL "simple" objetivo (ej: .../index.php?wc-api=Openpay_Stores)
     * @return array ['status' => 'found'|'created', 'id' => string]
     * @throws \Exception Si la API falla en un paso crítico (getList o create)
     */
    public function reconcileWebhooks(string $target_url_pretty, string $target_url_simple): array
    {
        // Esta es la parte de la URL que SIEMPRE identifica a nuestro webhook,
        // sin importar el dominio o el subdirectorio.
        $canonical_api_path = '/wc-api/Openpay_Stores';
        $canonical_simple_path = '/index.php';
        $canonical_simple_query = 'wc-api=Openpay_Stores';

        // Obtener todos los webhooks
        $all_webhooks = $this->getAllWebhooks();

        $current_webhook_found = null;
        $old_webhooks_to_delete = [];

        // Clasificar webhooks
        foreach ($all_webhooks as $webhook) {
            $webhook_path = parse_url($webhook->url, PHP_URL_PATH);
            $webhook_query = parse_url($webhook->url, PHP_URL_QUERY);
            $webhook_url = $webhook->url;

            $this->logger->info('--- COMPARANDO WEBHOOK ---');
            $this->logger->info('Webhook URL: ' . $webhook_url);
            $this->logger->info('Target URL (Pretty): ' . $target_url_pretty);
            $this->logger->info('Target URL (Simple): ' . $target_url_simple);

            // Verificación 1: ¿Es el webhook actual (coincidencia exacta)?
            // Comparamos contra ambas URLs (pretty y simple) por si acaso.
            if ($webhook_url === $target_url_pretty || $webhook_url === $target_url_simple) {
                $this->logger->info('Resultado: Encontrado webhook activo (Coincidencia exacta).');
                $current_webhook_found = $webhook;
            }
            // Verificación 2: ¿Es "nuestro" webhook pero en otro dominio/subdirectorio (obsoleto)?
            else {
                // Comprobamos si el path TERMINA con nuestro endpoint canónico
                // Usamos substr() para compatibilidad con PHP < 8 (str_ends_with es PHP 8+)
                $is_our_pretty_webhook = (substr($webhook_path, -strlen($canonical_api_path)) === $canonical_api_path);

                // Comprobamos si es nuestra URL simple (el path debe terminar en index.php y el query debe coincidir)
                $is_our_simple_webhook = (substr($webhook_path, -strlen($canonical_simple_path)) === $canonical_simple_path) &&
                    (strcasecmp(trim($webhook_query), $canonical_simple_query) === 0);

                if ($is_our_pretty_webhook || $is_our_simple_webhook) {
                    $this->logger->info('Resultado: Encontrado webhook obsoleto (Coincide endpoint pero no URL completa). Marcando para borrar.');
                    $old_webhooks_to_delete[] = $webhook;
                } else {
                    $this->logger->info('Resultado: Webhook ignorado (no coincide con el endpoint).');
                }
            }
        }

        // Elimina los webhooks obsoletos
        foreach ($old_webhooks_to_delete as $webhook_to_delete) {
            try {
                $this->deleteWebhook($webhook_to_delete->id); //
                $this->logger->info('Webhook obsoleto eliminado: ' . $webhook_to_delete->id);
            } catch (\Exception $e) {
                $this->logger->error('Error al intentar eliminar el webhook obsoleto ' . $webhook_to_delete->id . ': ' . $e->getMessage());
            }
        }

        // Crear o retornar
        if (is_null($current_webhook_found)) {
            $this->logger->info('El webhook actual no existe. Creando uno nuevo...');
            $newWebhook = $this->createWebhook($target_url_pretty); //
            $this->logger->info('Nuevo webhook creado exitosamente: ' . $newWebhook->id);
            return ['status' => 'created', 'id' => $newWebhook->id];
        } else {
            $this->logger->info('El webhook actual ya estaba registrado. Verificación completa.');
            return ['status' => 'found', 'id' => $current_webhook_found->id];
        }
    }

}
