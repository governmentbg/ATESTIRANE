<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Models\User;
use App\Models\Attestation;
use App\Models\AttestationForm;
use App\Models\Organisation;
use Session;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('view_attestation_form_toolbar', function (User $user) {
            $current_role = Session::get('role_id');
            if( in_array($current_role, [2, 3, 5]) ){
                return true;
            } else {
                return false;
            }
        });

        Gate::define('view_attestation_form', function (User $user, $attestation_form_id) {
            $current_role = Session::get('role_id');
            $current_attestation_id = Session::get('attestation_id');
            $attestation = Attestation::find($current_attestation_id);
            $attestation_form = AttestationForm::find($attestation_form_id);
            if( $attestation_form ){
                if( $current_role == 1 ){
                    return true;
                }
                // Вход като Локален администратор
                if( $current_role == 2 ){
                    $local_admin_organisaton = $user->organisation_id;
                    $local_parent_organisation = Organisation::get_grand_parent_organisation($local_admin_organisaton);
                    
                    $attestation_form_user_organisation = $attestation_form->user->organisation_id;
                    $user_parent_organisation = Organisation::get_grand_parent_organisation($attestation_form_user_organisation);
                    if( $local_parent_organisation == $user_parent_organisation ){
                        return true;
                    }
                }

                if( $attestation_form->attestation_id == $attestation->id ){
                    // Вход като Оценяващ ръководител
                    if( $current_role == 3 && $attestation_form->director_id == $user->id ){
                        return true;
                    }
                    // Вход като Оценяван
                    if( $current_role == 4 && $attestation_form->user_id == $user->id ){
                        return true;
                    }
                    // Вход като Член на атестационна комисия
                    if( $current_role == 5 ){
                        $valid_evaluated_members = [];
                        $commissions = $user->commissions()->where('attestation_id', $attestation->id)->get();
                        foreach( $commissions as $commission ){
                            foreach( $commission->evaluated_members as $evaluated_member){
                                $valid_evaluated_members[] = $evaluated_member->id;
                            }
                        }
                        if( in_array($attestation_form->user_id, $valid_evaluated_members) ){
                            return true;
                        }
                    }
                }
            }
            return false;
        });

        Gate::define('edit_attestation_form', function (User $user, $attestation_form_id) {
            $attestation = Session::get('attestation');
            // Ако атестацият е приключила, няма право да се редактира формуляра
            if( $attestation->status == 'completed' ){
                return false;
            }

            $current_role = Session::get('role_id');

            $attestation_form = AttestationForm::where('id', $attestation_form_id)->where('attestation_id', $attestation->id)->first();
            if( $attestation_form ){
                // Формуляра е започнал оценяване, следователно НЕ разрешаваме редакция
                if( $attestation_form->scores && $attestation_form->scores->scores ){
                    return false;
                }
                // Вход като Оценяван
                if( $current_role == 4 && $attestation_form->user_id == $user->id ){
                    return true;
                }
                // Вход като Оценяващ ръководител
                if( $current_role == 3 && $attestation_form->director_id == $user->id ){
                    return true;
                }
            }
            return false;
        });

        Gate::define('evaluate_attestation_form', function (User $user, $attestation_form_id) {
            $current_role = Session::get('role_id');
            $attestation = Session::get('attestation');
            // Ако атестацият е приключила, няма право да се оценява формуляра
            if( $attestation->status == 'completed' ){
                return false;
            }
            // Ако формуляра е приключен, няма право да се оценява отново
            $attestation_form = AttestationForm::where('id', $attestation_form_id)->where('attestation_id', $attestation->id)->first();
            if( $attestation_form ){
                if( in_array($attestation_form->status, ['wait_final_score', 'completed']) ){
                    return false;
                }
                if( !$attestation_form->active_goals || $attestation_form->active_goals->goals_status != 'signed' ){
                    return false;
                }
                // Член на атестационна комисия
                if( $current_role == 5 ){
                    $valid_evaluated_members = [];
                    $commissions = $user->commissions()->where('attestation_id', $attestation->id)->get();
                    foreach( $commissions as $commission ){
                        foreach( $commission->evaluated_members as $evaluated_member){
                            $valid_evaluated_members[] = $evaluated_member->id;
                        }
                    }
                    // Проверяваме дали този ЧАК може да оценява този формуляр
                    if( in_array($attestation_form->user_id, $valid_evaluated_members) ){
                        return true;
                    }
                }
            }
            return false;
        });

        Gate::define('sign_attestation_form', function (User $user, $attestation_form_id) {
            $current_role = Session::get('role_id');
            $current_attestation_id = Session::get('attestation_id');
            $attestation = Attestation::find($current_attestation_id);
            $attestation_form = AttestationForm::where('id', $attestation_form_id)->where('attestation_id', $attestation->id)->first();
            if( $attestation_form ){
                // Ако атестацият е приключила, няма право да се оценява формуляра
                if( $attestation->status == 'completed' ){
                    return false;
                }
                // Ако формуляра е приключен, няма право да се оценява отново
                
                if( in_array($attestation_form->status, ['wait_final_score', 'completed']) ){
                    return false;
                }
                // Член на атестационна комисия
                if( $current_role == 5 ){
                    $valid_evaluated_members = [];
                    $commissions = $user->commissions()->where('attestation_id', $attestation->id)->get();
                    foreach( $commissions as $commission ){
                        foreach( $commission->evaluated_members as $evaluated_member){
                            $valid_evaluated_members[] = $evaluated_member->id;
                        }
                    }
                    // Проверяваме дали този ЧАК може да оценява този формуляр
                    if( in_array($attestation_form->user_id, $valid_evaluated_members) ){
                        return true;
                    }
                }
                // Оценяван
                if( $current_role == 4 && $attestation_form->user_id == $user->id ){
                    return true;
                }
            }
            return false;
        });

        Gate::define('finalize_attestation_form', function (User $user, $attestation_form_id){
            $current_role = Session::get('role_id');
            if( $current_role == 2 ){
                $attestation_form = AttestationForm::find($attestation_form_id);
                if( $attestation_form ){
                    if( $attestation_form->status != 'wait_final_score' ){
                        return false;
                    }
                    $local_admin_organisaton = $user->organisation_id;
                    $local_parent_organisation = Organisation::get_grand_parent_organisation($local_admin_organisaton);
                    $attestation_form_user_organisation = $attestation_form->user->organisation_id;
                    $user_parent_organisation = Organisation::get_grand_parent_organisation($attestation_form_user_organisation);
                    if( $local_parent_organisation == $user_parent_organisation ){
                        return true;
                    } else {
                        return false;
                    }
                }
            }
            return false;
        });

        Gate::define('manage_organizations', function (User $user) {
            $current_role = Session::get('role_id');
            if( $current_role == 1 || $current_role == 2 ){
                return true;
            }
            return false;
        });

        Gate::define('manage_commissions', function (User $user) {
            $current_role = Session::get('role_id');
            if( $current_role == 2 ){
                return true;
            }
            return false;
        });

        Gate::define('manage_positions', function (User $user) {
            $current_role = Session::get('role_id');
            if( $current_role == 1 ){
                return true;
            }
            return false;
        });

        Gate::define('manage_central_administrators', function (User $user) {
            $current_role = Session::get('role_id');
            if( $current_role == 1 ){
                return true;
            }
            return false;
        });

        Gate::define('manage_local_administrators', function (User $user) {
            $current_role = Session::get('role_id');
            if( $current_role == 1 || $current_role == 2 ){
                return true;
            }
            return false;
        });

        Gate::define('manage_scores', function (User $user) {
            $current_role = Session::get('role_id');
            if( $current_role == 1 ){
                return true;
            }
            return false;
        });

        Gate::define('manage_employees', function (User $user) {
            $current_role = Session::get('role_id');
            if( $current_role == 1 || $current_role == 2 ){
                return true;
            }
            return false;
        });
    }
}
