<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Validation\ValidationException;

/**
 * LedgerService — бизнес-логика главной книги.
 *
 * Отвечает за:
 * - валидацию правила двойной записи (дебет = кредит)
 * - сохранение проводок (journal entries) к транзакции
 */
class LedgerService
{
    /**
     * Проверяет, что сумма дебетовых проводок равна сумме кредитовых.
     *
     * @param  array  $entries  Массив проводок: [['account_id'=>.., 'amount'=>.., 'type'=>..], ...]
     * @throws ValidationException если баланс не сходится
     */
    public function validateBalance(array $entries): void
    {
        $debit  = 0.0;
        $credit = 0.0;

        foreach ($entries as $entry) {
            $amount = (float) ($entry['amount'] ?? 0);
            $type   = $entry['type'] ?? '';

            if ($type === 'debit') {
                $debit += $amount;
            } elseif ($type === 'credit') {
                $credit += $amount;
            }
        }

        // Допускаем погрешность в 0.001 из-за особенностей float-арифметики
        if (abs($debit - $credit) > 0.001) {
            throw ValidationException::withMessages([
                'entries_data' => sprintf(
                    'Дебет (%.2f) не равен кредиту (%.2f). Разница: %.2f',
                    $debit,
                    $credit,
                    abs($debit - $credit)
                ),
            ]);
        }
    }

    /**
     * Сохраняет проводки к транзакции.
     * Сначала удаляет старые проводки, затем создаёт новые.
     *
     * @param  Transaction  $transaction
     * @param  array        $entries
     */
    public function saveEntries(Transaction $transaction, array $entries): void
    {
        // Валидируем баланс перед сохранением
        $this->validateBalance($entries);

        // Удаляем старые проводки
        $transaction->journalEntries()->delete();

        // Создаём новые проводки
        foreach ($entries as $entry) {
            if (empty($entry['account_id']) || empty($entry['amount']) || empty($entry['type'])) {
                continue;
            }

            $transaction->journalEntries()->create([
                'account_id' => $entry['account_id'],
                'amount'     => $entry['amount'],
                'type'       => $entry['type'],
            ]);
        }
    }

    /**
     * Вычисляет итоговый баланс по проводкам транзакции.
     * Возвращает ['debit' => float, 'credit' => float]
     *
     * @param  Transaction  $transaction
     * @return array{debit: float, credit: float}
     */
    public function getBalance(Transaction $transaction): array
    {
        $entries = $transaction->journalEntries;

        return [
            'debit'  => (float) $entries->where('type', 'debit')->sum('amount'),
            'credit' => (float) $entries->where('type', 'credit')->sum('amount'),
        ];
    }
}
