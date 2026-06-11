<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Проверяем базовые поля транзакции и сами проводки.
     * Минимум две проводки нужен, потому что операция должна быть сбалансированной.
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'entries' => ['required', 'array', 'min:2'],
            'entries.*.account_id' => ['required', 'integer', 'exists:accounts,id'],
            'entries.*.amount' => ['required', 'numeric', 'min:0.01'],
            'entries.*.type' => ['required', Rule::in(['debit', 'credit'])],
        ];
    }
}
