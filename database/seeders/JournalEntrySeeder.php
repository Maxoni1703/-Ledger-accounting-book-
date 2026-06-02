<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use RuntimeException;

class JournalEntrySeeder extends Seeder
{
    public function run(): void
    {
        $cash = Account::query()->where('code', '1010')->first();
        $bank = Account::query()->where('code', '1030')->first();
        $revenue = Account::query()->where('code', '6010')->first();
        $expense = Account::query()->where('code', '7010')->first();

        $openingTransaction = Transaction::query()
            ->whereDate('date', '2026-06-01')
            ->where('description', 'Стартовый остаток и ввод начальных данных')
            ->first();

        $incomeTransaction = Transaction::query()
            ->whereDate('date', '2026-06-02')
            ->where('description', 'Поступление денежных средств от клиента')
            ->first();

        $expenseTransaction = Transaction::query()
            ->whereDate('date', '2026-06-03')
            ->where('description', 'Оплата текущих расходов')
            ->first();

        if (! $cash || ! $bank || ! $revenue || ! $expense || ! $openingTransaction || ! $incomeTransaction || ! $expenseTransaction) {
            throw new RuntimeException('Seed accounts or transactions are missing.');
        }

        $entries = [
            [
                'transaction_id' => $openingTransaction->id,
                'account_id' => $cash->id,
                'amount' => 50000.00,
                'type' => 'debit',
            ],
            [
                'transaction_id' => $openingTransaction->id,
                'account_id' => $revenue->id,
                'amount' => 50000.00,
                'type' => 'credit',
            ],
            [
                'transaction_id' => $incomeTransaction->id,
                'account_id' => $bank->id,
                'amount' => 12000.00,
                'type' => 'debit',
            ],
            [
                'transaction_id' => $incomeTransaction->id,
                'account_id' => $revenue->id,
                'amount' => 12000.00,
                'type' => 'credit',
            ],
            [
                'transaction_id' => $expenseTransaction->id,
                'account_id' => $expense->id,
                'amount' => 3500.00,
                'type' => 'debit',
            ],
            [
                'transaction_id' => $expenseTransaction->id,
                'account_id' => $cash->id,
                'amount' => 3500.00,
                'type' => 'credit',
            ],
        ];

        foreach ($entries as $entry) {
            JournalEntry::updateOrCreate(
                [
                    'transaction_id' => $entry['transaction_id'],
                    'account_id' => $entry['account_id'],
                    'type' => $entry['type'],
                ],
                $entry
            );
        }
    }
}
