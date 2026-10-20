<?php

namespace App\Services\Business\System;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupService
{
    public function createBackup(): array
    {
        $timestamp = Carbon::now()->format('Ymd_His');
        $fileName = "backup_agrohisab_{$timestamp}.json";

        // Dump core trading tables to archive JSON
        $tables = [
            'units',
            'products',
            'suppliers',
            'aratdars',
            'markets',
            'accounts',
            'purchases',
            'purchase_items',
            'shipments',
            'shipment_items',
            'shipment_sales',
            'stock_movements',
            'supplier_payments',
            'aratdar_remittances',
            'remittance_allocations',
            'settlements',
            'expenses',
        ];

        $dump = [
            'meta' => [
                'system'     => 'AgroHisab Business Service',
                'created_at' => Carbon::now()->toIso8601String(),
                'version'    => '1.0',
            ],
            'tables' => [],
        ];

        foreach ($tables as $t) {
            try {
                $dump['tables'][$t] = DB::table($t)->get()->toArray();
            } catch (\Throwable $e) {
                // Table might not exist or empty
            }
        }

        $json = json_encode($dump, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $diskPath = "backups/{$fileName}";

        Storage::disk('local')->put($diskPath, $json);

        return [
            'filename'   => $fileName,
            'file_size'  => strlen($json),
            'path'       => $diskPath,
            'created_at' => Carbon::now()->toIso8601String(),
        ];
    }

    public function listBackups(): array
    {
        $files = Storage::disk('local')->files('backups');
        $list = [];

        foreach ($files as $f) {
            if (str_ends_with($f, '.json')) {
                $list[] = [
                    'filename'   => basename($f),
                    'path'       => $f,
                    'file_size'  => Storage::disk('local')->size($f),
                    'created_at' => Carbon::createFromTimestamp(Storage::disk('local')->lastModified($f))->toIso8601String(),
                ];
            }
        }

        return array_reverse($list);
    }
}