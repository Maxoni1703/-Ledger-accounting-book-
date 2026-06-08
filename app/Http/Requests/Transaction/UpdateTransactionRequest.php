<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['sometimes', 'date'],
            'description' => ['nullable', 'string'],
            'entries' => ['sometimes', 'array', 'min:2'],
            'entries.*.account_id' => ['required_with:entries', 'integer', 'exists:accounts,id'],
            'entries.*.amount' => ['required_with:entries', 'numeric', 'min:0.01'],
            'entries.*.type' => ['required_with:entries', Rule::in(['debit', 'credit'])],
        ];
    }
}
