<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\CpfHelper;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'cpf' => 'required|digits:11|unique:users,cpf',
            'type' => 'required|in:common,shopkeeper',
            'balance' => 'nullable|numeric|min:0',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $cpf = $this->input('cpf');

            if (!\App\Helpers\CpfHelper::isValidCpf($cpf)) {
                $validator->errors()->add('cpf', 'CPF inválido.');
            }
        });
    }
}
