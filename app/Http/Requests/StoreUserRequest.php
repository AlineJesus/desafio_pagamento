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
            'password_confirmation' => 'required|string|min:8',
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
            'full_name.required' => 'O nome completo é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'document.unique' => 'Este CPF ou CNPJ já está cadastrado.',
            'document.required' => 'O CPF ou CNPJ é obrigatório.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'balance.numeric' => 'O saldo deve ser um número.',
            'balance.min' => 'O saldo não pode ser negativo.',
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
            $document = $this->input('document', '');
            $document = preg_replace('/\D/', '', $document); // Remove caracteres não numéricos

            $isCpfValid = User::isValidCpf($document);
            $isCnpjValid = User::isValidCnpj($document);

            if (! $isCpfValid && ! $isCnpjValid) {
                $validator->errors()->add('document', 'Documento inválido.');

                return;
            }

            // Define o tipo automaticamente com base no documento
            $type = $isCpfValid ? 'common' : 'shopkeeper';

            // Adiciona o tipo aos dados da requisição
            $this->merge(['type' => $type]);
        });
    }
}
