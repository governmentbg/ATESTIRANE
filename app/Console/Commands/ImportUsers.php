<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Storage;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Models\User;
use App\Models\Position;

use Carbon\Carbon;
use Validator;
use Illuminate\Validation\Rule;
use App\Rules\ValidEGN;

class ImportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for importing users from excel file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $files_to_process = array_filter(Storage::disk('local')->files('import_users'), function ($item) {
            $valid_name = str_starts_with($item, 'import_users/import_users');
            $valid_ext = strpos($item, '.xlsx');
            return ($valid_name && $valid_ext);
        });

        if( !$files_to_process ){
            $this->error('Липсват валидни файлове за импортиране!');
            return 0;
        }

        foreach( $files_to_process as $filename ){
            $valid = true;
            $all_records = 0;
            $processed = 0;
            $not_processed = [];

            $array = Excel::toArray(new UsersImport, storage_path('app/'.$filename));

            $data = $array[0];

            $heading_row = $data[1];
            array_splice($data, 0, 2);

            // Проверка, дали е качен правилен файл - първия ред е с фиксираните имена на колоните
            $heading_columns = [
                0 => "ЕГН**",
                1 => "Служебно досие или Оценяващ**",
                2 => "Имена**",
                3 => "Електронна поща**",
                4 => "Ранг*",
                5 => "Начин за придобиване на ранга",
                6 => "Организационно звено**",
                7 => "Длъжност*",
                8 => "Вид длъжност*",
                9 => "Подлежи на електронна атестация*",
                10 => "Дата на назначаване(на встъпване)*",
                11 => "Дата на преназначаване",
                12 => "Дата на завръщане (от дълъг отпуск/майчинство)",
                13 => "Оценка 1",
                14 => "Година 1",
                15 => "Оценка 2",
                16 => "Година 2",
                17 => "Оценка",
                18 => "Година",
                19 => "Локален администратор(да/не)",
                // 20 => "Централен администратор(да/не)"
            ];
            foreach( $heading_columns as $key => $value ){
                if( !isset($heading_row[$key]) || $heading_row[$key] != $value ){
                    $valid = false;
                    break;
                }
            }

            if( $valid ){
                foreach($data as $user_info){
                    // Попълнено е ЕГН, следователно обработваме реда
                    if($user_info[0]){
                        $all_records++;
                        $user_info[3] = trim($user_info[3]);
                        if( $user_info[1] == 'Служебно досие' ){
                            $fields = [
                                '0' => ['required', 'unique:users,egn', new ValidEGN],
                                '1' => 'required',
                                '2' => 'required',
                                '3' => 'email|required',
                                '4' => 'required',
                                '5' => '',
                                '6' => 'required',
                                '7' => 'required',
                                '8' => '',
                                '9' => 'required',
                                '10' => 'required'
                            ];
                        } else if( $user_info[1] == 'Оценяващ' ){
                            $fields = [
                                '0' => ['required', 'unique:users,egn', new ValidEGN],
                                '1' => 'required',
                                '2' => 'required',
                                '3' => 'email|required',
                                '4' => '',
                                '5' => '',
                                '6' => 'required'
                            ];
                        }

                        $validator = Validator::make($user_info, $fields); 
                        // невалиден запис
                        if ($validator->fails()){
                            //dump($user_info, $validator->errors());
                            array_push($not_processed, $user_info[0]);
                        } 
                        // валиден запис
                        else {
                            $user = User::where('egn', $user_info[0])->first();
                            if( $user ){
                                array_push($not_processed, $user_info[0]);
                            } else {
                                $processed++;
                                $user = new User;
                                $organisation_id = null;
                                list($organisation_id, $organisation_name) = explode('|', $user_info[6]);
                                if( $organisation_id ){
                                    if( $user_info[1] == 'Оценяващ' ){
                                        $user->only_evaluate = 1;
                                        $user->name = $user_info[2];
                                        $user->egn = $user_info[0];
                                        $user->email = $user_info[3];
                                        $user->digital_attestation = 0;
                                        $user->organisation_id = $organisation_id;
                                        $user->save();
                                    } else {
                                        list($nkpd, $position_name) = explode('|', $user_info[7]);
                                        list($nkpd1, $nkpd2) = explode('-', $nkpd);
                                        switch( $user_info[8] ){
                                            case 'Ръководна':
                                                $position_type = 'management';
                                                break;
                                            case 'Експертна':
                                                $position_type = 'experts';
                                                break;
                                            case 'Обща/Специализирана администрация':
                                                $position_type = 'general';
                                                break;
                                            case 'Техническа':
                                                $position_type = 'technical';
                                                break;
                                            case 'Специфична':
                                                $position_type = 'specific';
                                                break;
                                            default:
                                                $position_type = 'management';
                                                break;
                                        }
                                        $position = Position::where('type', $position_type)->where('nkpd1', $nkpd1)->where('nkpd2', $nkpd2)->first();
                                        // $position = Position::where('nkpd1', $nkpd1)->where('nkpd2', $nkpd2)->first();
                                        if( $position ){
                                            $user->only_evaluate = 0;
                                            $user->name = $user_info[2];
                                            $user->egn = $user_info[0];
                                            $user->organisation_id = $organisation_id;
                                            $user->email = $user_info[3];
                                            $user->rank = $user_info[4];
                                            if( $user_info[5] == 'Предсрочно' ){
                                                $user->rank_acquisition = 'early';
                                            } else {
                                                $user->rank_acquisition = 'normal';
                                            }

                                            $user->position_id = $position->id;
                                            $user->digital_attestation = ($user_info[9] == 'Да' ? 1:0);

                                            if( $user_info[10] ){
                                                if( strtotime($user_info[10]) ){
                                                    $appointment_date = date('Y-m-d', strtotime($user_info[10]));
                                                } else {
                                                    $appointment_date = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($user_info[10]))->format('Y-m-d');
                                                }
                                            } else {
                                                $appointment_date = null;
                                            }
                                            $user->appointment_date = $appointment_date;
                                            if( $user_info[11] ){
                                                if( strtotime($user_info[11]) ){
                                                    $reassignment_date = date('Y-m-d', strtotime($user_info[11]));
                                                } else {
                                                    $reassignment_date = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($user_info[11]))->format('Y-m-d');
                                                }
                                            } else {
                                                $reassignment_date = null;
                                            }
                                            $user->reassignment_date = $reassignment_date;
                                            $user->leaving_date = null;
                                            if( $user_info[12] ){
                                                if( strtotime($user_info[12]) ){
                                                    $returning_date = date('Y-m-d', strtotime($user_info[12]));
                                                } else {
                                                    $returning_date = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($user_info[12]))->format('Y-m-d');
                                                }
                                            } else {
                                                $returning_date = null;
                                            }
                                            $user->returning_date = $returning_date;

                                            $user->old_attestation_year_1 = $user_info[13];
                                            $user->old_attestation_score_1 = $user_info[14];
                                            $user->old_attestation_year_2 = $user_info[15];
                                            $user->old_attestation_score_2 = $user_info[16];
                                            $user->save();

                                            if( $user_info[19] == 'Да' ){
                                                $user->roles()->syncWithoutDetaching(2);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            Storage::move($filename, 'done_'.$filename);
            if( $valid ){
                $this->info('Импортиранетo на потребители от файл "'.$filename.'" завърши успешно!');
                $this->warn('Брой записи във файла: '.$all_records);
                $this->info('Обработени записи: '.$processed);
                $this->line('Необработени записи (пр. липсващи полета, повтарящи се записи): '.sizeof($not_processed)); 
            } else {
                $this->info('Импортиранетo на потребители от файл "'.$filename.'"');
                $this->error('Грешен формат на файла за импорт!');
            }
            $this->line('-------------');   
        }
        $this->line('Процесът по импортиране на потребители завърши!');   
	//if( $not_processed ){
	//	dump($not_processed);
	//}         
	return 0;
    }
}
