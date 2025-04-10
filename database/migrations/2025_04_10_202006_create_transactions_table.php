<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Executa as migrações.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // Chaves estrangeiras para usuários
            $table->unsignedBigInteger('payer_id');
            $table->unsignedBigInteger('payee_id');

            // Valor da transação
            $table->decimal('amount', 15, 2);

            $table->timestamps();

            // Definindo as foreign keys
            $table->foreign('payer_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('payee_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverte as migrações.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
