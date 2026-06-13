<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Email;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email_id' => Email::factory(),
            'filename' => fake()->word().'.txt',
            'filepath' => 'attachments/sample-'.fake()->uuid().'.txt',
            'filesize' => fake()->numberBetween(512, 8096),
            'mime_type' => 'text/plain',
        ];
    }
}
