<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_transaction_with_valid_entries(): void
    {
        $user = User::factory()->create();
        $cash = Account::factory()->create();
        $revenue = Account::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/transactions', [
                'date' => '2026-06-11',
                'description' => 'Поступление денег',
                'entries' => [
                    [
                        'account_id' => $cash->id,
                        'amount' => 500.00,
                        'type' => 'debit',
                    ],
                    [
                        'account_id' => $revenue->id,
                        'amount' => 500.00,
                        'type' => 'credit',
                    ],
                ],
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('transactions', [
            'description' => 'Поступление денег',
        ]);
        $this->assertDatabaseHas('journal_entries', [
            'account_id' => $cash->id,
            'amount' => '500.00',
            'type' => 'debit',
        ]);
        $this->assertDatabaseHas('journal_entries', [
            'account_id' => $revenue->id,
            'amount' => '500.00',
            'type' => 'credit',
        ]);
    }

    public function test_cannot_create_transaction_with_unbalanced_entries(): void
    {
        $user = User::factory()->create();
        $cash = Account::factory()->create();
        $revenue = Account::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/transactions', [
                'date' => '2026-06-11',
                'description' => 'Некорректная проводка',
                'entries' => [
                    [
                        'account_id' => $cash->id,
                        'amount' => 500.00,
                        'type' => 'debit',
                    ],
                    [
                        'account_id' => $revenue->id,
                        'amount' => 400.00,
                        'type' => 'credit',
                    ],
                ],
            ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('transactions', [
            'description' => 'Некорректная проводка',
        ]);
    }
}
