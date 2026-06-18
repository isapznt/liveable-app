<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition()
    {
        return [
            'local' => $this->faker->word(),
            'type' => $this->faker->word(),
            'beds_qtd' => $this->faker->randomNumber(),
            'toilette' => $this->faker->randomNumber(),
            'area' => $this->faker->randomNumber(),
            'owner_contact' => $this->faker->word(),
            'property_title' => $this->faker->word(),
            'wifi' => $this->faker->boolean(),
            'tv' => $this->faker->boolean(),
            'cooler' => $this->faker->boolean(),
            'air_conditioning' => $this->faker->boolean(),
            'washer' => $this->faker->boolean(),
            'microwave' => $this->faker->boolean(),
            'contract' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
