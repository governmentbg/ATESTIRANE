<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Storage;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Models\User;
use App\Models\Position;

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
        $filename = 'import_users.xlsx';
        if(!Storage::disk('local')->exists('import_users/'.$filename)){
            abort(404);
        }
        
        // $file = Storage::get('import_users/'.$filename);
        // $type = Storage::mimeType('import_users/'.$filename);

        $array = Excel::toArray(new UsersImport, storage_path('app/import_users/'.$filename));

        $data = $array[0];

        $heading_row = $data[1];
        array_splice($data, 0, 2);

        // Проверка, дали е качен правилен файл - първия ред е с фиксираните имена на колоните
        $heading_columns = [
            0 => "ЕГН",
            1 => "Служебно досие или Оценяващ",
            2 => "Имена",
            3 => "Електронна поща",
            4 => "Ранг",
            5 => "Начин за придобиване на ранга",
            6 => "Организационно звено",
            7 => "Длъжност",
            8 => "Вид длъжност",
            9 => "Подлежи на електронна атестация(да/не)",
            10 => "Дата на назначаване",
            11 => "Дата на преназначаване",
            12 => "Дата на завръщане (от дълъг отпуск/майчинство)",
            13 => "Оценка 1",
            14 => "Година 1",
            15 => "Оценка 2",
            16 => "Година 2",
            17 => "Локален администратор(да/не)",
            18 => "Централен администратор(да/не)"
        ];
        foreach( $heading_columns as $key => $value ){
            if( !isset($heading_row[$key]) || $heading_row[$key] != $value ){
                $this->error('Грешен формат на файла за импорт!');
                return 0;
            }
        }

        // dd($data);
        $all_records = 0;
        $processed = 0;
        $not_processed = [];
        foreach($data as $user_info){
            // Попълнено е ЕГН, следователно обработваме реда
            if($user_info[0]){
                $all_records++;
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
                        '8' => 'required',
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
                                    $user->appointment_date = $user_info[10] ? date('Y-m-d', strtotime($user_info[10])) : null;
                                    $user->reassignment_date = $user_info[11] ? date('Y-m-d', strtotime($user_info[11])) : null;
                                    $user->leaving_date = null;
                                    $user->returning_date = $user_info[12] ? date('Y-m-d', strtotime($user_info[12])) : null;;

                                    $user->old_attestation_year_1 = $user_info[13];
                                    $user->old_attestation_score_1 = $user_info[14];
                                    $user->old_attestation_year_2 = $user_info[15];
                                    $user->old_attestation_score_2 = $user_info[16];
                                    $user->save();

                                    if( $user_info[17] == 'Да' ){
                                        $user->roles()->syncWithoutDetaching(2);
                                    }

                                    if( $user_info[18] == 'Да' ){
                                        $user->roles()->syncWithoutDetaching(1);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->info('Импортиранетo на потребители завърши успешно!');
        $this->warn('Брой записи във файла: '.$all_records);
        $this->info('Обработени записи: '.$processed);
        $this->line('Необработени записи (пр. липсващи полета, повтарящи се записи): '.sizeof($not_processed));
        return 0;
    }
}
