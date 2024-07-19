<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TickerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tickers')->insert([
            'type' => 'App\Models\Ticker',
            'index' => '1000',
            'meta' => null,
        ]);
    }
}
