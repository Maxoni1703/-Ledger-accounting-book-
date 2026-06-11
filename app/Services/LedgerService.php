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
     * Это основное правило двойной записи: одна операция должна закрываться
     * одинаковыми суммами по дебету и кредиту.
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
     * Сначала удаляем старые записи, потому что операция может быть обновлена,
     * и нам нужен только актуальный набор проводок.
     *
     * @param  Transaction  $transaction
     * @param  array        $entries
     */
    public function saveEntries(Transaction $transaction, array $entries): void
    {
        $this->validateBalance($entries);

        $transaction->journalEntries()->delete();
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

    /**
     * Формирует оборотно-сальдовую ведомость для выбранного периода.
     * Здесь считаем открытый остаток до периода, обороты внутри периода и
     * итоговый остаток на конец периода.
     *
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return array
     */
    public function getTrialBalance(?string $dateFrom, ?string $dateTo): array
    {
        $accounts = \App\Models\Account::where('is_active', true)->orderBy('code')->get();
        $report = [];

        foreach ($accounts as $account) {
            $queryBefore = \App\Models\JournalEntry::where('account_id', $account->id)
                ->whereHas('transaction', function ($q) use ($dateFrom) {
                    if ($dateFrom) {
                        $q->whereDate('date', '<', $dateFrom);
                    } else {
                        // Если период не задан, открытый остаток должен быть пустым.
                        $q->whereRaw('1 = 0');
                    }
                });

            $openingDebit = (float) (clone $queryBefore)->where('type', 'debit')->sum('amount');
            $openingCredit = (float) (clone $queryBefore)->where('type', 'credit')->sum('amount');
            
            $openingBalanceDebit = 0;
            $openingBalanceCredit = 0;
            
            if (in_array($account->type, ['asset', 'expense'])) {
                $openingBalanceDebit = max(0, $openingDebit - $openingCredit);
                $openingBalanceCredit = max(0, $openingCredit - $openingDebit);
            } else {
                // Для пассивов, капитала и доходов остаток чаще уходит в кредит.
                $openingBalanceCredit = max(0, $openingCredit - $openingDebit);
                $openingBalanceDebit = max(0, $openingDebit - $openingCredit);
            }

            $queryPeriod = \App\Models\JournalEntry::where('account_id', $account->id)
                ->whereHas('transaction', function ($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom) {
                        $q->whereDate('date', '>=', $dateFrom);
                    }
                    if ($dateTo) {
                        $q->whereDate('date', '<=', $dateTo);
                    }
                });

            $turnoverDebit = (float) (clone $queryPeriod)->where('type', 'debit')->sum('amount');
            $turnoverCredit = (float) (clone $queryPeriod)->where('type', 'credit')->sum('amount');

            $closingDebit = $openingDebit + $turnoverDebit;
            $closingCredit = $openingCredit + $turnoverCredit;

            $closingBalanceDebit = 0;
            $closingBalanceCredit = 0;

            if (in_array($account->type, ['asset', 'expense'])) {
                $closingBalanceDebit = max(0, $closingDebit - $closingCredit);
                $closingBalanceCredit = max(0, $closingCredit - $closingDebit);
            } else {
                $closingBalanceCredit = max(0, $closingCredit - $closingDebit);
                $closingBalanceDebit = max(0, $closingDebit - $closingCredit);
            }

            if ($openingBalanceDebit > 0 || $openingBalanceCredit > 0 || $turnoverDebit > 0 || $turnoverCredit > 0 || $closingBalanceDebit > 0 || $closingBalanceCredit > 0) {
                $report[] = [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'opening_debit' => $openingBalanceDebit,
                    'opening_credit' => $openingBalanceCredit,
                    'turnover_debit' => $turnoverDebit,
                    'turnover_credit' => $turnoverCredit,
                    'closing_debit' => $closingBalanceDebit,
                    'closing_credit' => $closingBalanceCredit,
                ];
            }
        }

        return $report;
    }
}
