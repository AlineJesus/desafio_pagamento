<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'document', //cfp ou cnpj
        'password',
        'type',    // 'common' ou 'shopkeeper'
        'balance', // Saldo do usuário
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'balance'           => 'decimal:2',
    ];

    /**
     * Relacionamento com as transações em que o usuário é o pagador.
     */
    public function transactionsAsPayer()
    {
        return $this->hasMany(Transaction::class, 'payer_id');
    }

    /**
     * Relacionamento com as transações em que o usuário é o recebedor.
     */
    public function transactionsAsPayee()
    {
        return $this->hasMany(Transaction::class, 'payee_id');
    }

    /**
     * Valida um CPF brasileiro.
     *
     * Este método remove caracteres não numéricos, verifica se o CPF possui 11 dígitos,
     * se não é composto por dígitos repetidos e se os dígitos verificadores estão corretos.
     *
     * @param string $cpf
     * @return bool
     */
    public static function isValidCpf(string $cpf): bool
    {
        // Remove qualquer caractere que não seja dígito
        $cpf = preg_replace('/\D/', '', $cpf);

        // Verifica se tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se todos os dígitos são iguais (ex: 11111111111), o que é inválido
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Validação dos dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += $cpf[$i] * (($t + 1) - $i);
            }
            // Calcula o dígito esperado:
            $expectedDigit = ((10 * $sum) % 11) % 10;
            if ($cpf[$t] != $expectedDigit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Valida um CNPJ brasileiro.
     *
     * @param string $cnpj
     * @return bool
     */
    public static function isValidCnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);

        if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        for ($t = 12; $t < 14; $t++) {
            $sum = 0;
            $pos = $t - 7;
            for ($i = 0; $i < $t; $i++) {
                $sum += $cnpj[$i] * $pos--;
                if ($pos < 2) {
                    $pos = 9;
                }
            }
            $expectedDigit = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);
            if ($cnpj[$t] != $expectedDigit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Valida o documento (CPF ou CNPJ) com base no tipo de usuário.
     *
     * @return bool
     */
    public function isValidDocument(): bool
    {
        if ($this->type === 'common') {
            return self::isValidCpf($this->document);
        }

        if ($this->type === 'shopkeeper') {
            return self::isValidCnpj($this->document);
        }

        return false;
    }

    /**
     * Evento para validar o documento antes de salvar.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($user) {
            if (!$user->isValidDocument()) {
                throw new \Exception('Documento inválido para o tipo de usuário.');
            }
        });
    }

}

/* 

Explicação dos Componentes
SoftDeletes:
Utilizamos o trait SoftDeletes para que, ao invés de excluir fisicamente os registros, seja marcado uma data na coluna deleted_at indicando que o registro foi removido logicamente.

$fillable:
Permite a atribuição em massa dos campos definidos. Isso é importante para proteger contra a atribuição não intencional de outros campos.

$hidden:
Define os campos que não devem ser expostos em arrays ou JSON, como a senha e o token de "remember me".

$casts:
Converte os atributos para tipos nativos. Aqui, o saldo é convertido para um decimal com 2 casas decimais e a verificação de e-mail, se estiver sendo utilizada, para datetime.

Relacionamentos:

transactionsAsPayer: Retorna todas as transações onde o usuário fez uma transferência.

transactionsAsPayee: Retorna todas as transações onde o usuário recebeu uma transferência.

Essa estrutura fornece uma base robusta para trabalhar com os usuários no contexto do desafio PicPay, considerando os requisitos de autenticação, autorização e relacionamentos com transações.*/