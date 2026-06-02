<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Касса',
                'code' => '1010',
                'type' => 'asset',
                'is_active' => true,
            ],
            [
                'name' => 'Расчетный счет',
                'code' => '1030',
                'type' => 'asset',
                'is_active' => true,
            ],
            [
                'name' => 'Выручка',
                'code' => '6010',
                'type' => 'revenue',
                'is_active' => true,
            ],
            [
                'name' => 'Расходы',
                'code' => '7010',
                'type' => 'expense',
                'is_active' => true,
            ],
        ];

        foreach ($accounts as $account) {
            Account::updateOrCreate(
                ['code' => $account['code']],
                $account
            );
        }
    }
}
