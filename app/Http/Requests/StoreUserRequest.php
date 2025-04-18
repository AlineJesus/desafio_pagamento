<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para a solicitação.
     *
     * @return array<string, string> Regras de validação
     */
    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'document' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'balance' => 'required|numeric',
            'type' => 'sometimes|string',
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
            'full_name.required' => 'Full name is required',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be valid.',
            'email.unique' => 'This email is already registered.',
            'document.unique' => 'This CPF or CNPJ is already registered.',
            'document.required' => 'CPF or CNPJ is mandatory.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'balance.numeric' => 'The balance must be a number.',
            'balance.min' => 'The balance cannot be negative.',
        ];
    }

    /**
     * Adiciona validações personalizadas após as regras padrão.
     *
     * A verificação usa is_scalar() para garantir segurança na conversão para string:
     * - is_scalar() retorna true apenas para tipos básicos (string, int, float, bool)
     * - Isso evita erros ao tentar converter arrays, objetos ou null para string
     * - Resolve o aviso do Larastan sobre type safety no casting misto
     *
     * @param  \Illuminate\Validation\Validator  $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var string $document */
            $document = $this->validated('document', '');
            $document = preg_replace('/\D/', '', $document);

            $isCpfValid = User::isValidCpf($document);
            $isCnpjValid = User::isValidCnpj($document);

            if (! $isCpfValid && ! $isCnpjValid) {
                $validator->errors()->add('document', 'Invalid document.');

                return;
            }

            // Define o tipo automaticamente com base no documento
            $type = $isCpfValid ? 'common' : 'shopkeeper';

            // Adiciona o tipo aos dados da requisição
            $this->merge(['type' => $type]);
        });
    }
}
