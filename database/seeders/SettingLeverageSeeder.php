<?php

namespace Database\Seeders;

use App\Models\SettingLeverage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingLeverageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SettingLeverage::create([
            'display' => '1:1',
            'value' => 1,
        ]);
        SettingLeverage::create([
            'display' => '1:10',
            'value' => 10,
        ]);
        SettingLeverage::create([
            'display' => '1:20',
            'value' => 20,
        ]);
        SettingLeverage::create([
            'display' => '1:50',
            'value' => 50,
        ]);
        SettingLeverage::create([
            'display' => '1:100',
            'value' => 100,
        ]);
        SettingLeverage::create([
            'display' => '1:200',
            'value' => 200,
        ]);
        SettingLeverage::create([
            'display' => '1:300',
            'value' => 300,
        ]);
        SettingLeverage::create([
            'display' => '1:400',
            'value' => 400,
        ]);
        SettingLeverage::create([
            'display' => '1:500',
            'value' => 500,
        ]);
    }
}
