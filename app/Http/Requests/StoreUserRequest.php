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
            'document' => 'required|string|unique:users,document', // Validação de unicidade do CPF ou CNPJ
            'password'  => 'required|string|min:8|confirmed',
            'type' => 'required|in:common,shopkeeper',
            'balance' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Mensagens de erro personalizadas.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'full_name.required' => 'O nome completo é obrigatório.',
            'email.required'     => 'O e-mail é obrigatório.',
            'email.email'        => 'O e-mail deve ser válido.',
            'email.unique'       => 'Este CPF ou CNPJ já está cadastrado.',
            'document.required'  => 'O CPF ou CNPJ é obrigatório.',
            'password.required'  => 'A senha é obrigatória.',
            'password.min'       => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não corresponde.',
            'type.required'      => 'O tipo de usuário é obrigatório.',
            'type.in'            => 'O tipo de usuário deve ser "common" ou "shopkeeper".',
            'balance.numeric'    => 'O saldo deve ser um número.',
            'balance.min'        => 'O saldo não pode ser negativo.',
        ];
    }

    /**
     * Adiciona validações personalizadas após as regras padrão.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $document = $this->input('document'); // CPF ou CNPJ
            $type = $this->input('type');

            if ($type === 'common' && !User::isValidCpf($document)) {
                $validator->errors()->add('document', 'CPF inválido.');
            }

            if ($type === 'shopkeeper' && !User::isValidCnpj($document)) {
                $validator->errors()->add('document', 'CNPJ inválido.');
            }
        });
    }
}
