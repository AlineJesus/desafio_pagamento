<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payer_id',
        'payee_id',
        'amount',
    ];

    /**
     * Relacionamento com o usuário que paga (remetente).
     *
     * @return BelongsTo<User, Transaction>
     */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    /**
     * Relacionamento com o usuário que recebe (destinatário).
     *
     * @return BelongsTo<User, Transaction>
     */
    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee_id');
    }
}
