<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function ceo(): static
    {
        return $this->afterCreating(fn (User $user) => $user->assignRole('ceo'));
    }

    public function manager(?string $department = null): static
    {
        return $this->afterCreating(function (User $user) use ($department): void {
            $user->assignRole('manager');

            if ($department !== null) {
                $managedDepartment = Department::where('slug->en', $department)->firstOrFail();
                $user->forceFill(['department_id' => $managedDepartment->id])->saveQuietly();
                $user->managedDepartments()->sync([$managedDepartment->id]);
            }
        });
    }

    public function hr(): static
    {
        return $this->afterCreating(fn (User $user) => $user->assignRole('hr'));
    }

    public function it(): static
    {
        return $this->afterCreating(fn (User $user) => $user->assignRole('it'));
    }
}
