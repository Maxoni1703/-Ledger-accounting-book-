<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use App\Models\Account;
use Illuminate\Http\JsonResponse;

class AccountController extends Controller
{
    public function index(): JsonResponse
    {
        $accounts = Account::orderBy('code')->get();

        return response()->json($accounts);
    }

    public function store(StoreAccountRequest $request): JsonResponse
    {
        $account = Account::create($request->validated());

        return response()->json($account, 201);
    }

    public function show(Account $account): JsonResponse
    {
        return response()->json($account);
    }

    public function update(UpdateAccountRequest $request, Account $account): JsonResponse
    {
        $account->update($request->validated());

        return response()->json($account);
    }

    public function destroy(Account $account): JsonResponse
    {
        if ($account->journalEntries()->exists()) {
            return response()->json([
                'message' => 'Счёт используется в проводках',
            ], 422);
        }

        $account->delete();

        return response()->json(null, 204);
    }
}
