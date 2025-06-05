<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DocumentDefaultService
{
    public function createDefaultsForCompany($companyId)
    {
        $timestamp = Carbon::now();

        $defaults = [
            [
                'company_id' => $companyId,
                'type' => 'invoice',
                'logo' => null,
                'show_logo' => false,
                'number_prefix' => 'INV-',
                'number_digits' => 5,
                'number_next' => 1,
                'payment_terms' => 'due_upon_receipt',
                'header' => 'Invoice',
                'subheader' => null,
                'terms' => null,
                'footer' => null,
                'accent_color' => '#4F46E5',
                'font' => 'inter',
                'template' => 'default',
                'item_name' => json_encode(['custom' => null, 'option' => 'items']),
                'unit_name' => json_encode(['custom' => null, 'option' => 'quantity']),
                'price_name' => json_encode(['custom' => null, 'option' => 'price']),
                'amount_name' => json_encode(['custom' => null, 'option' => 'amount']),
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'company_id' => $companyId,
                'type' => 'bill',
                'logo' => null,
                'show_logo' => false,
                'number_prefix' => 'BILL-',
                'number_digits' => 5,
                'number_next' => 1,
                'payment_terms' => 'due_upon_receipt',
                'header' => 'Bill',
                'subheader' => null,
                'terms' => null,
                'footer' => null,
                'accent_color' => '#4F46E5',
                'font' => 'inter',
                'template' => 'default',
                'item_name' => json_encode(['custom' => null, 'option' => 'items']),
                'unit_name' => json_encode(['custom' => null, 'option' => 'quantity']),
                'price_name' => json_encode(['custom' => null, 'option' => 'price']),
                'amount_name' => json_encode(['custom' => null, 'option' => 'amount']),
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ];

        DB::table('document_defaults')->insert($defaults);
    }
}
