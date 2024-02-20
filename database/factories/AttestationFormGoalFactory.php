<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AttestationFormGoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attestation_form_id' => 1,
            'goals' => [
                "goals" => [
                    0 => [
                        "goal" => "Тест", 
                        "result" => "тест", 
                        "date_to" => "31.10.2023", 
                        "date_from" => "12.10.2023"
                    ]
                ]
            ],
            'goals_status' => 'preview'
        ];
    }
}
