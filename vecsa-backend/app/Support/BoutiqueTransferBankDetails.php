<?php

namespace App\Support;

use App\Models\SystemSetting;

/**
 * Datos bancarios para transferencia en checkout boutique (SystemSetting).
 */
final class BoutiqueTransferBankDetails
{
    /**
     * @return array{bank_name: string, account_holder: string, clabe: string, account_number: string, instructions: string, configured: bool}
     */
    public static function publicPayload(): array
    {
        $bankName = trim((string) SystemSetting::get('boutique_transfer_bank_name', ''));
        $holder = trim((string) SystemSetting::get('boutique_transfer_account_holder', ''));
        $clabe = trim((string) SystemSetting::get('boutique_transfer_clabe', ''));
        $account = trim((string) SystemSetting::get('boutique_transfer_account_number', ''));
        $instructions = trim((string) SystemSetting::get('boutique_transfer_instructions', ''));

        $configured = $bankName !== '' && $holder !== '' && $clabe !== '';

        return [
            'bank_name' => $bankName,
            'account_holder' => $holder,
            'clabe' => $clabe,
            'account_number' => $account,
            'instructions' => $instructions,
            'configured' => $configured,
        ];
    }

    public static function isConfigured(): bool
    {
        return self::publicPayload()['configured'];
    }
}
