<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Blog>
 */
class BlogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'summary' => $this->faker->text(100), // ✅ Add this line
            'body' => $this->faker->paragraph,
            'image_url' => $this->faker->imageUrl(),
            'tags' => [$this->faker->word, $this->faker->word],
            'person_id' => \App\Models\Person::factory(),
        ];
    }



}
