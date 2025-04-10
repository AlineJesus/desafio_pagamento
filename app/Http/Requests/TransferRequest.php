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
        // Aqui você pode adicionar lógica de autorização, se necessário.
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
            'payer_id' => 'required|exists:users,id',
            'payee_id' => 'required|exists:users,id|different:payer_id',
            'amount'   => 'required|numeric|min:0.01',
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
            $payer = User::find($this->input('payer_id'));

            if ($payer && $payer->type !== 'common') {
                $validator->errors()->add('payer_id', 'Somente usuários do tipo "common" podem realizar transferências.');
            }
        });
    }
}
