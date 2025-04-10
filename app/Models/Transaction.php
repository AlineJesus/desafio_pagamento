<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'payer_id',
        'payee_id',
        'amount',
    ];

    /**
     * Relacionamento com o usuário que paga (remetente).
     */
    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    /**
     * Relacionamento com o usuário que recebe (destinatário).
     */
    public function payee()
    {
        return $this->belongsTo(User::class, 'payee_id');
    }
}
