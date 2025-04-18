<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransferRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer essa requisição.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Define as regras de validação para os dados enviados.
     *
     * @return array<string, string> Regras de validação
     */
    public function rules(): array
    {
        return [
            'value' => 'required|numeric|min:0.01', // Valor da transferência
            'payee' => 'required|exists:users,id|different:payer', // ID do recebedor
        ];
    }

    /**
     * Define as mensagens de erro para as validações.
     *
     * @return array<string, string> Mensagens de erro
     */
    public function messages(): array
    {
        return [
            'value.required' => 'Transfer amount is required.',
            'value.numeric' => 'Transfer amount must be numeric.',
            'value.min' => 'Transfer amount must be greater than zero.',
            'payer.required' => 'Payer is required.',
            'payer.exists' => 'Payer does not exist.',
            'payee.required' => 'Payee is required.',
            'payee.exists' => 'Payee does not exist.',
            'payee.different' => 'Payer and recipient must be different.',
        ];
    }

    /**
     * Validações adicionais após as regras básicas.
     *
     * Aqui, verificamos se o usuário que está enviando o dinheiro (payer) é do tipo "common".
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $payer = auth()->user(); // Usuário autenticado

            if ($payer->type !== 'common') {
                $validator->errors()->add('payer', 'Only "common" type users can perform transfers.');
            }
        });
    }

    /**
     * Retorna o ID do pagador como inteiro.
     */
    public function payer(): int
    {
        return is_numeric($this->input('payer')) ? (int) $this->input('payer') : 0;
    }

    /**
     * Retorna o ID do recebedor como inteiro.
     */
    public function payee(): int
    {
        return is_numeric($this->input('payee')) ? (int) $this->input('payee') : 0;
    }

    /**
     * Retorna o valor da transferência como float.
     */
    public function value(): float
    {
        return is_numeric($this->input('value')) ? (float) $this->input('value') : 0.0;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
