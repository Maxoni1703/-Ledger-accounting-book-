<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Transaction;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LedgerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_validate_balance_passes_when_debit_equals_credit(): void
    {
        $service = new LedgerService();

        $entries = [
            ['account_id' => 1, 'amount' => 100, 'type' => 'debit'],
            ['account_id' => 2, 'amount' => 100, 'type' => 'credit'],
        ];

        $service->validateBalance($entries);

        $this->assertTrue(true);
    }

    public function test_validate_balance_throws_when_debit_not_equal_credit(): void
    {
        $service = new LedgerService();

        $entries = [
            ['account_id' => 1, 'amount' => 100, 'type' => 'debit'],
            ['account_id' => 2, 'amount' => 90, 'type' => 'credit'],
        ];

        $this->expectException(ValidationException::class);

        $service->validateBalance($entries);
    }

    public function test_get_balance_returns_correct_sums(): void
    {
        $accountDebit = Account::factory()->create();
        $accountCredit = Account::factory()->create();

        $transaction = Transaction::create([
            'date' => '2026-06-11',
            'description' => 'Test transaction',
        ]);

        JournalEntry::create([
            'transaction_id' => $transaction->id,
            'account_id' => $accountDebit->id,
            'amount' => 150,
            'type' => 'debit',
        ]);

        JournalEntry::create([
            'transaction_id' => $transaction->id,
            'account_id' => $accountCredit->id,
            'amount' => 150,
            'type' => 'credit',
        ]);

        $service = new LedgerService();

        $balance = $service->getBalance($transaction);

        $this->assertSame(150.0, $balance['debit']);
        $this->assertSame(150.0, $balance['credit']);
    }
}
