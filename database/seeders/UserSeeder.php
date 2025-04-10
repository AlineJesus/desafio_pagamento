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
        ]);

        User::factory()->create([
            'full_name' => 'Lojista',
            'email' => 'lojista@teste.com',
            'document' => '40125694000108',///CNPJ
            'type' => 'shopkeeper',
            'balance' => 1000,
        ]);
    }
}
