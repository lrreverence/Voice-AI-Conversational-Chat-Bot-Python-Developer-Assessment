<?php

namespace Modules\ContactManager\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\ContactManager\Entities\Contact;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\ContactManager\Entities\Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
