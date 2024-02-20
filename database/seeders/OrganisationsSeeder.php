<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Organisation;

class OrganisationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file = storage_path('app/organisations.csv');
        //import CSV function
        function import_CSV($filename, $delimiter = ','){
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
        $organisations = import_CSV($file);
        $db_organisations = Organisation::all();
        if( $db_organisations->isEmpty() ){
          Organisation::upsert($organisations, ['id'], ['parent_id', 'name', 'status']);
        }
    }
}
