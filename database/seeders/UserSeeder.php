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
            'cpf' => '855.788.140-18',
            'type' => 'common',
            'balance' => 1000,
        ]);

        User::factory()->create([
            'full_name' => 'Lojista',
            'email' => 'lojista@teste.com',
            'cpf' => '707.955.920-00',
            'type' => 'shopkeeper',
            'balance' => 1000,
        ]);
    }
}
