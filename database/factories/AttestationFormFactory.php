<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use App\Models\AttestationForm;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class AttestationFormFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attestation_id' => 1,
            'user_id' => 1,
            'commission_id' => 1,
            'director_id' => 1,
            'type' => 'general',
            'personal_data' => '{
                "name": "Тест Потребител",
                "to_date": "30.11.2024",
                "position": "Директор на дирекция",
                "from_date": "01.08.2023",
                "organisation": "Администрация на ВСС",
                "administration": "Администрация на ВСС"
            }',
            'status' => 'in_progress',
            // 'final_score' => '',
            // 'final_score_signed' => '',
            // 'final_score_signed_at' => '',
            // 'final_score_signed_by' => '',
            // 'final_score_comment' => '',
        ];
    }

    protected $model = AttestationForm::class;
}
