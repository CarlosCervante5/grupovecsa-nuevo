<?php

namespace Database\Factories;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente al factory.
     *
     * @var string
     */
    protected $model = Quiz::class;

    /**
     * Define la estructura por defecto del modelo.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'question_type' => 'affinity',
            'status' => 'active',
            'element_type' => 'checkbox',
            'group_name' => 'profile_affinities',
            'image_path' => $this->faker->imageUrl(640, 480, 'rewards', true),
        ];
    }
}
