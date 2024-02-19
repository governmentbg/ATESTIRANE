<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Position;

class PositionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file = storage_path('app/positions.csv');
        //import CSV function
        function import_positions_CSV($filename, $delimiter = ','){
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

        // store returned data into array of records
        $positions = import_positions_CSV($file);
        $db_positions = Position::all();
        if( $db_positions->isEmpty() ){
          Position::upsert($positions, ['nkpd1', 'nkpd2'], ['name', 'type']);
        }
    }
}
