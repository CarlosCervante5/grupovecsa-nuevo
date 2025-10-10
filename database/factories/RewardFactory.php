<?php

namespace Database\Factories;

use App\Models\Reward;
use Illuminate\Database\Eloquent\Factories\Factory;

class RewardFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente al factory.
     *
     * @var string
     */
    protected $model = Reward::class;

    /**
     * Define la estructura por defecto del modelo.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'begin_date' => $this->faker->date('Y-m-d'),
            'end_date' => $this->faker->date('Y-m-d'),
            'category' => $this->faker->randomElement(['ride', 'sale', 'promotion', 'event', 'service', 'valuation', 'consignation', 'profile', 'other']),
            'type' => $this->faker->randomElement(['discount', 'voucher', 'cashback']),
            'image_path' => $this->faker->imageUrl(640, 480, 'rewards', true),
        ];
    }
}
