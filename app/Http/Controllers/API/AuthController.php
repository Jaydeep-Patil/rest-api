<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use Auth;
use Validator;
use bcrypt;

class AuthController extends Controller
{
    //
    public function register(Request $request){
        $user_input = $request->all();
        if(isset($request->phone_no)){
            $rules = [
                'username' =>'required',
                'phone_no' => 'required|unique:users,phone_no',
                'email' => 'required|email|unique:users',
            ];
        }else{
            $rules = [
                'username' =>'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8|max:8',
            ];
            $user_input['password'] = bcrypt($user_input['password']);
        }
        //validate request
        $validator = Validator::make($request->all(),$rules);

        if($validator->fails()){
            $response = [
                'success' => false,
                'message' => $validator->errors()
            ];         
            return response()->json($response,400);   
        }else{
            $user = User::create($user_input);
            $success['token'] = $user->createToken('MyApp')->plainTextToken;
            $success['user_data'] = $user;

            $response = [
                'success' => true,
                'data' => $success,
                'message' => 'User register successfully',
            ];

            return response()->json($response,200);
        }
    }

    public function login(Request $request){
        $user = [];
        if(isset($request->username) && isset($request->password)){ 
            $valid = Auth::attempt(['username'=>$request->username, 'password'=>$request->password]);
                if($valid){
                    $user = Auth::user();
                } 
        }else if(isset($request->phone_no)){ 
            $user = User::where('phone_no', $request->phone_no)->first();
        }
        
        if(!empty($user)){
            $success['token'] = $user->createToken('MyApp')->plainTextToken;
            $success['user_data'] = $user;
            $response = [
                'success' => true,
                'data' => $success,
                'message' => 'User loggin successfully',
            ];
            return response()->json($response,200); 
        }else{
            $response = [
                'success' => false,
                'message' => 'Invalid credentials',
            ]; 
            return response()->json($response,401);
        }
    }
}
