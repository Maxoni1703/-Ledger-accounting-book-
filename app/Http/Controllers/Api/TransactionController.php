<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Services\LedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function __construct(
        private LedgerService $ledger
    ) {}

    public function index(): JsonResponse
    {
        $transactions = Transaction::with('journalEntries.account')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return response()->json($transactions);
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $transaction = DB::transaction(function () use ($data) {
            $transaction = Transaction::create([
                'date' => $data['date'],
                'description' => $data['description'] ?? null,
            ]);

            $this->ledger->saveEntries($transaction, $data['entries']);

            return $transaction->load('journalEntries.account');
        });

        return response()->json($transaction, 201);
    }

    public function show(Transaction $transaction): JsonResponse
    {
        $transaction->load('journalEntries.account');

        return response()->json($transaction);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $data = $request->validated();

        $transaction = DB::transaction(function () use ($transaction, $data) {
            $transaction->update(collect($data)->only(['date', 'description'])->filter()->all());

            if (isset($data['entries'])) {
                $this->ledger->saveEntries($transaction, $data['entries']);
            }

            return $transaction->load('journalEntries.account');
        });

        return response()->json($transaction);
    }

    public function destroy(Transaction $transaction): JsonResponse
    {
        $transaction->delete();

        return response()->json(null, 204);
    }
}
