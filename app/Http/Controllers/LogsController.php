<?php

namespace App\Http\Controllers;

use Auth;
use Session;
use Validator;
use App\Models\User;
use App\Models\LogEvent;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }
    
    public function list(Request $request)
    {
        $logs_temp = LogEvent::orderBy('id', 'asc');
        
        // search
        if($request->filled('type')){
            $logs_temp->where('type', '=', $request->input('type'));
        }
        
        if($request->filled('user_id')){
            $logs_temp->where('user_id', '=', $request->input('user_id'));
        }
        
        if($request->filled('date_range')){
            $date_range = explode(' - ', $request->input('date_range'));
            
            $from_date = date('Y-m-d', strtotime($date_range[0]));
            $to_date = date('Y-m-d', strtotime($date_range[1]));
            
            $logs_temp->whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $to_date);
        }
        
        /*if($request->filled('name')){
            $logs_temp->where('name', 'like', '%'.$request->input('name').'%');
        }*/
        
        $logs = $logs_temp->paginate(10);
        
        foreach($logs as $log){
            $log->user = User::find($log->user_id);
        }
        
        $users = User::orderBy('id', 'asc')->get();
        
        return view('logs.list', compact('logs', 'users'));
    }
}
