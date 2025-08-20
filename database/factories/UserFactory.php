<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition()
    {
        return [
            "name" => $this->faker->name(),
            "email" => $this->faker->unique()->safeEmail(),
            "email_verified_at" => now(),
            "cpf_cnpj" => $this->faker->numerify(str_repeat("#", 11)), // 11 dígitos (CPF fake)
            "type" => "comum",             // default coerente com o domínio
            "balance" => 0,                // default neutro
            "password" => "\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi", // "password"
            "remember_token" => Str::random(10),
        ];
    }
}
