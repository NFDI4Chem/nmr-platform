<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $devices = [
            [
                'manufacturer' => 'Agilent',
                'model_no' => '400-MR',
            ],
            [
                'manufacturer' => 'Agilent',
                'model_no' => 'Au 400 (DDR2 Console)',
            ],
            [
                'manufacturer' => 'Agilent',
                'model_no' => 'DDR 2 w/ HCN cryoprobe',
            ],
            [
                'manufacturer' => 'Bruker',
                'model_no' => 'AC',
            ],
            [
                'manufacturer' => 'Bruker',
                'model_no' => 'AMX',
            ],
            [
                'manufacturer' => 'Bruker',
                'model_no' => 'Avance I',
            ],
            [
                'manufacturer' => 'Bruker',
                'model_no' => 'AVANCE II',
            ],
            [
                'manufacturer' => 'Bruker',
                'model_no' => 'AVANCE III HD',
            ],
            [
                'manufacturer' => 'Bruker',
                'model_no' => 'AVANCE IVDr',
            ],
            [
                'manufacturer' => 'Bruker',
                'model_no' => 'Capillary LC-NMR',
            ],
            [
                'manufacturer' => 'Bruker',
                'model_no' => 'DNP-NMR',
            ],
            [
                'manufacturer' => 'Bruker',
                'model_no' => 'DRX 600',
            ],
            [
                'manufacturer' => 'Bruker',
                'model_no' => 'Food-Screener',
            ],
            [
                'manufacturer' => 'Bruker',
                'model_no' => 'Fourier 300HD',
            ],
            [
                'manufacturer' => 'Bruker',
                'model_no' => 'LC-NMR',
            ],
            [
                'manufacturer' => 'Bruker',
                'model_no' => 'LC-NMR/MS',
            ],
            [
                'manufacturer' => 'Bruker',
                'model_no' => 'Metabolic Profiler',
            ],
            [
                'manufacturer' => 'JEOL',
                'model_no' => 'JNM-ECA Series FT NMR',
            ],
            [
                'manufacturer' => 'JEOL',
                'model_no' => 'JNM-ECX Series FT NMR',
            ],
            [
                'manufacturer' => 'JEOL',
                'model_no' => 'JNM-ECXR Series FT NMR',
            ],
            [
                'manufacturer' => 'JEOL',
                'model_no' => 'JNM-ECZS Series FT NMR',
            ],
            [
                'manufacturer' => 'tecmag',
                'model_no' => 'CAT',
            ],
            [
                'manufacturer' => 'Varian',
                'model_no' => '400-MR',
            ],
            [
                'manufacturer' => 'Varian',
                'model_no' => 'DDR2',
            ],
            [
                'manufacturer' => 'Varian',
                'model_no' => 'GEMINI',
            ],
            [
                'manufacturer' => 'Varian',
                'model_no' => 'INOVA',
            ],
            [
                'manufacturer' => 'Varian',
                'model_no' => 'MERCURY',
            ],
            [
                'manufacturer' => 'Varian',
                'model_no' => 'UNITY',
            ],
            [
                'manufacturer' => 'Varian',
                'model_no' => 'UnityInova',
            ],
            [
                'manufacturer' => 'Varian',
                'model_no' => 'VNMRS',
            ],
            [
                'manufacturer' => 'Varian',
                'model_no' => 'VXR',
            ],
        ];

        // Randomly select 10 devices
        $selectedDevices = collect($devices)->random(10)->map(function ($device, $index) {
            return [
                'name' => Str::random(10).' NMR Device',
                'manufacturer' => $device['manufacturer'],
                'model_no' => $device['model_no'],
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        DB::table('devices')->insert($selectedDevices->toArray());
    }
}
