<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $transactions = [
            [
                'date' => '2026-06-01',
                'description' => 'Стартовый остаток и ввод начальных данных',
            ],
            [
                'date' => '2026-06-02',
                'description' => 'Поступление денежных средств от клиента',
            ],
            [
                'date' => '2026-06-03',
                'description' => 'Оплата текущих расходов',
            ],
        ];

        foreach ($transactions as $transaction) {
            Transaction::updateOrCreate(
                [
                    'date' => $transaction['date'],
                    'description' => $transaction['description'],
                ],
                $transaction
            );
        }
    }
}
