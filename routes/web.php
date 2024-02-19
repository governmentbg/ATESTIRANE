<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\ChecksController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\AssessorsController;
use App\Http\Controllers\PositionsController;
use App\Http\Controllers\GoalsScoreController;
use App\Http\Controllers\TotalScoreController;
use App\Http\Controllers\AttestationController;
use App\Http\Controllers\CommissionsController;
use App\Http\Controllers\OrganisationsController;
use App\Http\Controllers\CompetenceScoreController;
use App\Http\Controllers\AttestationformsController;
use App\Http\Controllers\LocalAdministratorsController;
use App\Http\Controllers\CentralAdministratorsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'login_post'])->name('login.post');
Route::get('/login-error', [AuthController::class, 'login_error'])->name('login.error');

Route::middleware(['auth'])->group(function () {
    Route::get('/choose_role', [AuthController::class, 'choose_role'])->name('choose.role');
    Route::post('/choose_role', [AuthController::class, 'choose_role_post'])->name('choose.role.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['has.role'])->group(function () {

        Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
        
        Route::get('image/{filename}', [HomeController::class, 'display_image'])->name('display_image');

        Route::middleware(['can:manage_employees'])->group(function () {
            Route::prefix('employees')->name('employees.')->group(function(){
                Route::get('/list', [EmployeesController::class, 'list'])->name('list');
                Route::get('/edit/{id?}', [EmployeesController::class, 'edit'])->name('edit');
                Route::post('/update', [EmployeesController::class, 'update'])->name('update');
                Route::get('/view/{id}', [EmployeesController::class, 'view'])->name('view');
                Route::get('/delete/{id}', [EmployeesController::class, 'delete'])->name('delete');
                Route::get('/search', [EmployeesController::class, 'show_search'])->name('show_search');
                Route::post('/search', [EmployeesController::class, 'search'])->name('search');
            });

            Route::prefix('assessors')->name('assessors.')->group(function(){
                Route::get('/list', [AssessorsController::class, 'list'])->name('list');
                Route::get('/edit/{id?}', [AssessorsController::class, 'edit'])->name('edit');
                Route::post('/update', [AssessorsController::class, 'update'])->name('update');
                Route::get('/delete/{id}', [AssessorsController::class, 'delete'])->name('delete');
                Route::get('/search', [AssessorsController::class, 'show_search'])->name('show_search');
                Route::post('/search', [AssessorsController::class, 'search'])->name('search');
            });
        });

        Route::middleware(['can:manage_commissions'])->group(function () {
            Route::prefix('commissions')->name('commissions.')->group(function(){
                Route::get('/list', [CommissionsController::class, 'list'])->name('list');
                Route::get('/edit/{id?}', [CommissionsController::class, 'edit'])->name('edit');
                Route::post('/update', [CommissionsController::class, 'update'])->name('update');
                Route::get('/view/{id}', [CommissionsController::class, 'view'])->name('view');
            });
        });

        Route::middleware(['can:manage_positions'])->group(function () {
            Route::prefix('positions')->name('positions.')->group(function(){
                Route::get('/types', [PositionsController::class, 'types'])->name('types');
                Route::get('/types/{type}', [PositionsController::class, 'list'])->name('list');
                Route::get('/edit/{id?}', [PositionsController::class, 'edit'])->name('edit');
                Route::post('/update', [PositionsController::class, 'update'])->name('update');
                Route::get('/delete/{id}', [PositionsController::class, 'delete'])->name('delete');
            });
        });

        Route::middleware(['can:manage_local_administrators'])->group(function () {
            Route::prefix('local_administrators')->name('local_administrators.')->group(function(){
                Route::get('/list', [LocalAdministratorsController::class, 'list'])->name('list');
                Route::get('/add', [LocalAdministratorsController::class, 'add'])->name('add');
                Route::post('/store', [LocalAdministratorsController::class, 'store'])->name('store');
                Route::get('/delete/{id}', [LocalAdministratorsController::class, 'delete'])->name('delete');
            });
        });

        Route::middleware(['can:manage_central_administrators'])->group(function () {
            Route::prefix('central_administrators')->name('central_administrators.')->group(function(){
                Route::get('/list', [CentralAdministratorsController::class, 'list'])->name('list');
                Route::get('/add', [CentralAdministratorsController::class, 'add'])->name('add');
                Route::post('/store', [CentralAdministratorsController::class, 'store'])->name('store');
                Route::get('/delete/{id}', [CentralAdministratorsController::class, 'delete'])->name('delete');
            });
        });
        
        Route::prefix('logs')->name('logs.')->group(function(){
            Route::get('/list', [LogsController::class, 'list'])->name('list');
        });
        
        Route::prefix('checks')->name('checks.')->group(function(){
            Route::get('/', [ChecksController::class, 'dashboard'])->name('dashboard');
            Route::get('/show', [ChecksController::class, 'show'])->name('show');
        });
        
        Route::middleware(['can:manage_scores'])->group(function () {
            Route::prefix('goals_score')->name('goals_score.')->group(function(){
                Route::get('/types', [GoalsScoreController::class, 'types'])->name('types');
                Route::get('/types/{type}', [GoalsScoreController::class, 'list'])->name('list');
                Route::get('/edit/{id?}', [GoalsScoreController::class, 'edit'])->name('edit');
                Route::post('/update', [GoalsScoreController::class, 'update'])->name('update');
                Route::get('/delete/{id}', [GoalsScoreController::class, 'delete'])->name('delete');
            });
            
            Route::prefix('competence_score')->name('competence_score.')->group(function(){
                Route::get('/types', [CompetenceScoreController::class, 'types'])->name('types');
                Route::get('/types/{type}', [CompetenceScoreController::class, 'list'])->name('list');
                Route::get('/edit/{id?}', [CompetenceScoreController::class, 'edit'])->name('edit');
                Route::post('/update', [CompetenceScoreController::class, 'update'])->name('update');
                Route::get('/delete/{id}', [CompetenceScoreController::class, 'delete'])->name('delete');
            });
            
            Route::prefix('total_score')->name('total_score.')->group(function(){
                Route::get('/types', [TotalScoreController::class, 'types'])->name('types');
                Route::get('/types/{type}', [TotalScoreController::class, 'list'])->name('list');
                Route::get('/edit/{id?}', [TotalScoreController::class, 'edit'])->name('edit');
                Route::post('/update', [TotalScoreController::class, 'update'])->name('update');
                Route::get('/delete/{id}', [TotalScoreController::class, 'delete'])->name('delete');
            });
        });
        
        Route::prefix('attestation-form')->name('attestationforms.')->group(function(){
            Route::get('/start', [AttestationformsController::class, 'start'])->name('start');

            Route::middleware(['can:view_attestation_form_toolbar'])->group(function () {
                Route::get('/list', [AttestationformsController::class, 'list'])->name('list');
            });

            Route::middleware(['can:view_attestation_form,id'])->group(function () {
                Route::get('/preview/{id}', [AttestationformsController::class, 'preview'])->name('preview');

                Route::middleware(['can:edit_attestation_form,id'])->group(function () {
                    // Route::get('/step-1/{id}', [AttestationformsController::class, 'step_1'])->name('step_1');

                    Route::prefix('step-2')->name('step_2.')->group(function(){
                        Route::get('/{id}', [AttestationformsController::class, 'step_2'])->name('view');
                        Route::post('/edit-mode/{id}', [AttestationformsController::class, 'step_2_edit_mode'])->name('edit_mode');
                        Route::post('/save/{id}', [AttestationformsController::class, 'step_2_save'])->name('save');
                        Route::post('/delete/{id}', [AttestationformsController::class, 'step_2_delete'])->name('delete');
                        Route::post('/operation/{id}', [AttestationformsController::class, 'step_2_operation'])->name('operation');
                        Route::post('/presign/{id}', [AttestationformsController::class, 'step_2_presign'])->name('presign');
                        Route::post('/sign/{id}', [AttestationformsController::class, 'step_2_sign'])->name('sign');
                    });
                    
                    Route::prefix('step-3')->name('step_3.')->group(function(){
                        Route::get('/{id}', [AttestationformsController::class, 'step_3'])->name('view');
                        Route::post('/request/{id}', [AttestationformsController::class, 'step_3_request'])->name('request');
                        Route::post('/save/{id}', [AttestationformsController::class, 'step_3_save'])->name('save');
                        Route::post('/presign/{id}', [AttestationformsController::class, 'step_3_presign'])->name('presign');
                        Route::post('/sign/{id}', [AttestationformsController::class, 'step_3_sign'])->name('sign');
                    });
                });

                Route::middleware(['can:evaluate_attestation_form,id'])->group(function () {
                    Route::prefix('step-4')->name('step_4.')->group(function(){
                        Route::get('/{id}', [AttestationformsController::class, 'step_4'])->name('view');
                        Route::post('/edit-mode/{id}', [AttestationformsController::class, 'step_4_edit_mode'])->name('edit_mode');
                        Route::post('/save/{id}', [AttestationformsController::class, 'step_4_save'])->name('save');
                        Route::post('/complete/{id}', [AttestationformsController::class, 'step_4_complete'])->name('complete');
                        Route::post('/sign/{id}', [AttestationformsController::class, 'step_4_sign'])->name('sign');
                    });
                });

                Route::middleware(['can:sign_attestation_form,id'])->group(function () {
                    Route::prefix('step-4')->name('step_4.')->group(function(){
                        Route::post('/presign/{id}', [AttestationformsController::class, 'step_4_presign'])->name('presign');
                        Route::post('/agree/{id}', [AttestationformsController::class, 'step_4_agree'])->name('agree');
                    });
                });

                Route::middleware(['can:finalize_attestation_form,id'])->group(function () {
                    Route::prefix('step-5')->name('step_5.')->group(function(){
                        Route::post('/presign/{id}', [AttestationformsController::class, 'step_5_presign'])->name('presign');
                        Route::post('/finalize/{id}', [AttestationformsController::class, 'step_5_finalize'])->name('finalize');
                    });
                });
            });
        });
        
        Route::middleware(['can:manage_organizations'])->group(function () {
            Route::prefix('organisations')->name('organisations.')->group(function(){
                Route::get('/list', [OrganisationsController::class, 'list'])->name('list');
                Route::get('/edit/{id?}', [OrganisationsController::class, 'edit'])->name('edit');
                Route::get('/add_to/{id}', [OrganisationsController::class, 'add_to'])->name('add_to');
                Route::post('/update', [OrganisationsController::class, 'update'])->name('update');
                Route::get('/change-status/{id}/{status}', [OrganisationsController::class, 'change_status'])->name('change_status');
                
                Route::get('/list_users', [OrganisationsController::class, 'list_users'])->name('list_users');
            });
        });
        
        Route::prefix('attestation')->name('attestation.')->group(function(){
            Route::get('/add', [AttestationController::class, 'add'])->name('add');
            Route::post('/update', [AttestationController::class, 'update'])->name('update');
        });
    });
});