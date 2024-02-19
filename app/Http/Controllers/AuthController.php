<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Validator;
use Session;
use App\Models\User;
use App\Models\Attestation;
use App\Models\AttestationForm;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function login()
    {
        $id_server_url = config('services.kep_authentication.url');
        return view('auth.login', compact('id_server_url'));
    }

    public function login_post(Request $request)
    {   
        if( $request->error ){
            return redirect()->route('login.error');
        }

        // Define the secret key
        $key = config('services.kep_authentication.encrypt_secret');
        // Define the encryption method
        $method = config('services.kep_authentication.encrypt_method');
        // Decode the encrypted data
        $encrypted = base64_decode($request->encrypted);
        // Extract the IV and the encrypted data
        $iv = substr($encrypted, 0, openssl_cipher_iv_length($method));
        $encrypted = substr($encrypted, openssl_cipher_iv_length($method));
        // Decrypt the data
        $decrypted = openssl_decrypt($encrypted, $method, $key, 0, $iv);
        $id_data = json_decode($decrypted);
        
        $client_cert_data = explode(',', $id_data->SSL_CLIENT_S_DN);
        while($cert_data = next($client_cert_data)){
            list($key,$val) = explode('=', $cert_data);
            $cert_data_attr[$key] = $val;
        }
        
        $egn = '';
        if( substr($cert_data_attr['serialNumber'], 0, 6) == 'PNOBG-' ){ 
            $egn = substr($cert_data_attr['serialNumber'], 6, 10);
        } 
        
        $user = User::where('egn', $egn)->first();
        if( $user ){
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->route('choose.role');
        } else {
            return redirect()->route('login')->with('error', 'Липсват данни за акаунт! Моля свържете се с локален администратор.');
        }
    }

    public function login_error(){
        return view('auth.error');
    }

    public function choose_role()
    {
        $user = Auth::user();

        $forms = $director_commissions = $commissions = [];
        if( $user->roles()->where('role_id', 3)->first() ){
            $director_commissions = $user->director_commissions()->orderBy('id', 'desc')->get()->unique('attestation_id');
        }
        if( $user->roles()->where('role_id', 4)->first() ){
            $forms = AttestationForm::where('user_id', $user->id)->orderBy('id', 'desc')->get();
        }
        if( $user->roles()->where('role_id', 5)->first() ){
            $commissions = $user->commissions()->orderBy('id', 'desc')->get()->unique('attestation_id');
        }

        return view('auth.choose_role', compact('user', 'forms', 'director_commissions', 'commissions'));
    }

    public function choose_role_post(Request $request)
    {   
        Session::put('role_id', $request->role);
        $role = Auth::user()->roles->where('id', $request->role)->first();
        if( !$role ){
            return redirect()->route('choose.role');
        }

        if( $request->attestation_id ){
            $attestation_id = $request->attestation_id;
            $attestation = Attestation::find($attestation_id);
        } else {
            $attestation = Attestation::where('status', 'active')->orderBy('created_at')->first();
            $attestation_id = $attestation->id;
        }

        Session::put('role', $role->name);
        Session::put('attestation_id', $attestation_id);
        Session::put('attestation', $attestation);
        
        log_event('user_logged_in', ['role_name' => $role->name]);
        
        // Оценяван
        if( in_array($request->role, ['3', '5']) ){
            return redirect()->route('attestationforms.list');
        }
        if( $request->role == '4' ){
            return redirect()->route('attestationforms.start');
        }
        return redirect()->route('dashboard');
    }

    public function logout(Request $request){
        Auth::logout();
 
        $request->session()->invalidate();
     
        $request->session()->regenerateToken();
     
        return redirect()->route('login');
    }
}
