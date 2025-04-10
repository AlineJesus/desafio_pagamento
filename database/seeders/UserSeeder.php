<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'full_name' => 'Usuário comum',
            'email' => 'comum@teste.com',
            'document' => '85578814018',//CPF
            'type' => 'common',
            'balance' => 1000,
            'password' => bcrypt('password123'), // Senha padrão
            'email_verified_at' => now(), // Verificação de e-mail
            'remember_token' => \Str::random(10), // Token de "lembrar-me"
        ]);

        User::factory()->create([
            'full_name' => 'Lojista',
            'email' => 'lojista@teste.com',
            'document' => '40125694000108',///CNPJ
            'type' => 'shopkeeper',
            'balance' => 1000,
            'password' => bcrypt('password123'), // Senha padrão
            'email_verified_at' => now(), // Verificação de e-mail
            'remember_token' => \Str::random(10), // Token de "lembrar-me"
        ]);
    }
}
