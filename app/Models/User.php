<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'full_name',
        'email',
        'document',
        'password',
        'type',
        'balance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'balance' => 'decimal:2',
    ];

    /**
     * Relacionamento com as transações em que o usuário é o pagador.
     *
     * @return HasMany<Transaction>
     */
    public function transactionsAsPayer(): HasMany
    {
        return $this->hasMany(Transaction::class, 'payer_id');
    }

    /**
     * Relacionamento com as transações em que o usuário é o recebedor.
     *
     * @return HasMany<Transaction>
     */
    public function transactionsAsPayee(): HasMany
    {
        return $this->hasMany(Transaction::class, 'payee_id');
    }

    /**
     * Valida se um CPF é válido.
     *
     * @param  string|null  $cpf  CPF a ser validado (pode ser formatado)
     * @return bool Retorna true se o CPF for válido
     */
    public static function isValidCpf(?string $cpf): bool
    {
        if ($cpf === null) {
            return false;
        }

        $cleanedCpf = preg_replace('/\D/', '', $cpf);

        if ($cleanedCpf === null || strlen($cleanedCpf) !== 11) {
            return false;
        }

        if (preg_match('/(\d)\1{10}/', $cleanedCpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $cleanedCpf[$i] * (($t + 1) - $i);
            }
            $expectedDigit = ((10 * $sum) % 11) % 10;
            if ((int) $cleanedCpf[$t] !== $expectedDigit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Valida se um CNPJ é válido.
     *
     * @param  string|null  $cnpj  CNPJ a ser validado (pode ser formatado)
     * @return bool Retorna true se o CNPJ for válido
     */
    public static function isValidCnpj(?string $cnpj): bool
    {
        if ($cnpj === null) {
            return false;
        }

        $cleanedCnpj = preg_replace('/\D/', '', $cnpj);

        if ($cleanedCnpj === null || strlen($cleanedCnpj) !== 14) {
            return false;
        }

        if (preg_match('/(\d)\1{13}/', $cleanedCnpj)) {
            return false;
        }

        for ($t = 12; $t < 14; $t++) {
            $sum = 0;
            $pos = $t - 7;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $cleanedCnpj[$i] * $pos--;
                if ($pos < 2) {
                    $pos = 9;
                }
            }
            $expectedDigit = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);
            if ((int) $cleanedCnpj[$t] !== $expectedDigit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Valida o documento com base no tipo
     *
     * @return bool Retorna true se o documento for válido para o tipo de usuário
     *
     * @throws \RuntimeException Se o tipo de usuário for inválido
     */
    public function isValidDocument(): bool
    {
        if ($this->document === null) {
            return false;
        }

        if ($this->type === null) {
            throw new \RuntimeException('Tipo de usuário não definido.');
        }

        return match ($this->type) {
            'common' => self::isValidCpf($this->document),
            'shopkeeper' => self::isValidCnpj($this->document),
            default => throw new \RuntimeException('Tipo de usuário inválido'),
        };
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (User $user) {
            // Se o tipo não estiver definido, determine-o automaticamente
            if ($user->type === null) {
                $document = preg_replace('/\D/', '', $user->document);
                $user->type = User::isValidCpf($document) ? 'common' : 'shopkeeper';
            }

            // Valida o documento após definir o tipo
            if (! $user->isValidDocument()) {
                throw new \RuntimeException('Documento inválido para o tipo de usuário.');
            }
        });
    }

    public function getNameWithEmailAttribute()
    {
        return "{$this->full_name} - {$this->email}";
    }
}
