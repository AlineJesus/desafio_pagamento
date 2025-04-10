<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'cpf' => $this->generateValidCpf(),
            'type' => $this->faker->randomElement(['common', 'shopkeeper']),
            'balance' => $this->faker->randomFloat(2, 10, 1000),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    private function generateValidCpf(): string
    {
        $n = [];
        for ($i = 0; $i < 9; $i++) {
            $n[$i] = random_int(0, 9);
        }

        $n[9] = $this->calculateCpfDigit($n, 10);
        $n[10] = $this->calculateCpfDigit($n, 11);

        return implode('', $n);
    }

    private function calculateCpfDigit(array $n, int $t): int
    {
        $sum = 0;
        for ($i = 0; $i < $t - 1; $i++) {
            $sum += $n[$i] * ($t - $i);
        }

        $digit = ($sum * 10) % 11;
        return ($digit == 10) ? 0 : $digit;
    }
}
