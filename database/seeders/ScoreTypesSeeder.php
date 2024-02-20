<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\GoalsScoreType;
use App\Models\CompetenceScoreType;
use App\Models\TotalScoreType;

class ScoreTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      //import CSV function
      function import_scores_CSV($filename, $delimiter = ','){
        if(!file_exists($filename) || !is_readable($filename))
        return false;
        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false){
          while (($row = fgetcsv($handle, 1000, $delimiter)) !== false){
            if(!$header)
              $header = $row;
                else
              $data[] = array_combine($header, $row);
          }
          fclose($handle);
        }
        return $data;
      }

      $goal_score_types_file = storage_path('app/goals_score_types.csv');
      $goal_score_types = import_scores_CSV($goal_score_types_file);
      $db_goal_score_types = GoalsScoreType::all();
      if( $db_goal_score_types->isEmpty() ){
        GoalsScoreType::upsert($goal_score_types, ['id'], ['attestation_form_type', 'text_score', 'points']);
      }

      $competence_score_types_file = storage_path('app/competence_score_types.csv');
      $competence_score_types = import_scores_CSV($competence_score_types_file);
      $db_competence_score_types = CompetenceScoreType::all();
      if( $db_competence_score_types->isEmpty() ){
        CompetenceScoreType::upsert($competence_score_types, ['id'], ['attestation_form_type', 'competence_type', 'text_score', 'points']);
      }

      $total_score_types_file = storage_path('app/total_score_types.csv');
      $total_score_types = import_scores_CSV($total_score_types_file);
      $db_total_score_types = TotalScoreType::all();
      if( $db_total_score_types->isEmpty() ){
        TotalScoreType::upsert($total_score_types, ['id'], ['attestation_form_type', 'text_score', 'from_points', 'to_points']);
      }
    }
}
