<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bank;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            ['name' => 'Bank of Ceylon', 'code' => 'BOC'],
            ['name' => 'Commercial Bank of Ceylon', 'code' => 'COMBANK'],
            ['name' => 'Hatton National Bank', 'code' => 'HNB'],
            ['name' => 'Sampath Bank', 'code' => 'SAMPATH'],
            ['name' => 'People\'s Bank', 'code' => 'PEOPLES'],
            ['name' => 'National Development Bank', 'code' => 'NDB'],
            ['name' => 'Nations Trust Bank', 'code' => 'NTB'],
            ['name' => 'Seylan Bank', 'code' => 'SEYLAN'],
            ['name' => 'DFCC Bank', 'code' => 'DFCC'],
            ['name' => 'Pan Asia Banking Corporation', 'code' => 'PABC'],
            ['name' => 'Amana Bank', 'code' => 'AMANA'],
            ['name' => 'HSBC Sri Lanka', 'code' => 'HSBC'],
            ['name' => 'Standard Chartered Sri Lanka', 'code' => 'SC'],
            ['name' => 'Cargills Bank', 'code' => 'CARGILLS'],
            ['name' => 'Union Bank of Colombo', 'code' => 'UBC'],
        ];

        foreach ($banks as $bank) {
            // This will find a bank by its name or create it if it doesn't exist.
            Bank::updateOrCreate(['name' => $bank['name']], [
                'code' => $bank['code'],
                'is_active' => true,
            ]);
        }
    }
}
//php artisan db:seed --class=BankSeeder  