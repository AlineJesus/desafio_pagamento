<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created_with_valid_data()
    {
        $payload = [
            'full_name' => 'Maria Souza',
            'email' => 'maria@example.com',
            'document' => '52998224725', // CPF válido
            'type' => 'common',
            'balance' => 100.00,
            'password' => 'password123', // Senha obrigatória
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'full_name' => 'Maria Souza',
                'email' => 'maria@example.com',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'maria@example.com',
        ]);
    }

    public function test_user_creation_fails_with_invalid_cpf()
    {
        $payload = [
            'full_name' => 'Carlos Silva',
            'email' => 'carlos@example.com',
            'document' => '12345678900', // CPF inválido
            'type' => 'common',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['document']);
    }

    public function test_user_creation_fails_with_missing_fields()
    {
        $response = $this->postJson('/api/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['full_name', 'email', 'document', 'type', 'password']);
    }

    public function test_user_creation_fails_with_duplicate_email()
    {
        User::factory()->create([
            'email' => 'joao@example.com',
        ]);

        $payload = [
            'full_name' => 'João da Silva',
            'email' => 'joao@example.com', // duplicado
            'cpf' => '12345678909',
            'type' => 'common',
            'balance' => 50.00,
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
