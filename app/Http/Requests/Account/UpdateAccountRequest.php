<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $accountId = $this->route('account');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:50', Rule::unique('accounts', 'code')->ignore($accountId)],
            'type' => ['sometimes', Rule::in(['asset', 'liability', 'equity', 'revenue', 'expense'])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
