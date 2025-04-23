<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Sample;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sample>
 */
class SampleFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            // 乱数
            'student_id' => fake()->numberBetween(1, 10),
            // 名前
            'sample_title' => fake()->name,
            // 日本語テキスト
            'sample_text' => fake()->realText(20),
            'regist_date' => fake()->date(),
            // 乱数
            'sample_state' => fake()->numberBetween(0, 1),
            'adm_id' => 200,
        ];
    }
}
