<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Cliente A',
            'email' => 'clienteA@example.com',
            'cpf_cnpj' => '11111111111',
            'type' => 'comum',
            'balance' => 1000,
            'password' => bcrypt('senha123'),
        ]);

        \App\Models\User::create([
            'name' => 'Lojista X',
            'email' => 'lojistaX@example.com',
            'cpf_cnpj' => '22222222000199',
            'type' => 'lojista',
            'balance' => 500,
            'password' => bcrypt('senha123'),
        ]);
    }

}


