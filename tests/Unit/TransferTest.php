<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa o endpoint de transferência.
     *
     * @return void
     */
    public function test_transfer_endpoint()
    {
        // Cria usuários para o teste
        $payer = User::factory()->create([
            'full_name' => 'Maria Souza',
            'email' => 'maria@example.com',
            'document' => '52998224725', // CPF válido
            'type' => 'common',
            'balance' => 100.00,
            'password' => 'password123', // Senha obrigatória
        ]);

        $payee = User::factory()->create([
            'type' => 'common',
            'document' => '40125694000108', // CNPJ válido
        ]);

        // Payload da requisição
        $payload = [
            'payer' => $payer->id,
            'payee' => $payee->id,
            'value' => 50.00,
        ];

        // Faz a requisição ao endpoint
        $response = $this->postJson('/api/transfer', $payload);

        // Verifica o status e a resposta
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Transferência realizada com sucesso.',
        ]);

        // Verifica se o saldo foi atualizado corretamente
        $this->assertDatabaseHas('users', [
            'id' => $payer->id,
            'balance' => 50.00,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $payee->id,
            'balance' => 50.00,
        ]);

        // Verifica erros de validação
        $response->assertJsonValidationErrors(['full_name', 'email', 'document', 'type', 'password']);
    }
}
