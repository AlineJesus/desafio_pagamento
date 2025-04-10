<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class TransferRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer essa requisição.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define as regras de validação para os dados enviados.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'value' => 'required|numeric|min:0.01', // Valor da transferência
            'payer' => 'required|exists:users,id',  // ID do pagador
            'payee' => 'required|exists:users,id|different:payer', // ID do recebedor
        ];
    }

    /**
     * Define as mensagens de erro para as validações.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'value.required' => 'O valor da transferência é obrigatório.',
            'value.numeric'  => 'O valor da transferência deve ser numérico.',
            'value.min'      => 'O valor da transferência deve ser maior que zero.',
            'payer.required' => 'O pagador é obrigatório.',
            'payer.exists'   => 'O pagador informado não existe.',
            'payee.required' => 'O recebedor é obrigatório.',
            'payee.exists'   => 'O recebedor informado não existe.',
            'payee.different' => 'O pagador e o recebedor devem ser diferentes.',
        ];
    }

    /**
     * Validações adicionais após as regras básicas.
     *
     * Aqui, verificamos se o usuário que está enviando o dinheiro (payer) é do tipo "common".
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $payer = User::find($this->input('payer'));

            if ($payer && $payer->type !== 'common') {
                $validator->errors()->add('payer', 'Somente usuários do tipo "common" podem realizar transferências.');
            }
        });
    }
}
