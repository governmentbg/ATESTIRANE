<?php

namespace App\Http\Controllers;

use Auth;
use Session;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function dashboard()
    {
        return view('dashboard');
    }
    
    public function display_image(string $filename){
        if(!Storage::disk('local')->exists('images/'.$filename)){
            abort(404);
        }
        
        $file = Storage::get('images/'.$filename);
        $type = Storage::mimeType('images/'.$filename);
        
        $response = Response::make($file, 200);
        $response->header('Content-type', $type);
        
        return $response;
    }
}
