<?php

namespace Database\Seeders;

use App\Models\CustomersModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CustomersModel::factory(50)->create();
    }
}
