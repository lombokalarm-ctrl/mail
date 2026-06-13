<?php

namespace Database\Factories;

use App\Models\Email;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Email>
 */
class EmailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inbox_id' => Inbox::factory(),
            'sender_email' => fake()->safeEmail(),
            'sender_name' => fake()->name(),
            'recipient_email' => fake()->userName().'@'.config('apli_mail.domain'),
            'subject' => fake()->sentence(5),
            'body_html' => '<p>'.fake()->paragraph().'</p>',
            'body_text' => fake()->paragraph(),
            'received_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ];
    }
}
