<?php

namespace App\Http\Controllers\V1\User\Auth;

use Auth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\V1\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class UserAuthController extends Controller
{

    use AuthenticatesUsers;

    /**
     * The name of the guard.
     * 
     * @var string
     */
    protected $guardName = 'users';


    /**
     * The authentication guard for the users .
     * 
     * @return [type] [description]
     */
    public function guard()
    {
    	return auth()->guard($this->guardName);
    }

    /**
     * Attempt to login the user.
     * 
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function login(Request $request)
    {
    	$this->validateLogin($request);

        $credentials = $request->only('email','password');

        if (!$token = $this->guard()->attempt($credentials)){
            return response()->json([
                'errors' => [
                    'email' => [
                        __('auth.failed')
                    ]
                ]
            ], 422);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Register a user.
     * 
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function register(Request $request)
    {
    	$this->validate($request, [
    		'name'	=>	'required',
    		'email'     => 'required|email|unique:users',
    		'phone'     => 'required|phone:NG|unique:users',
            'password'  => 'required|min:6',
    	]);

    	try
    	{
    		$user = new User();
	    	$user->name = $request->name;
	    	$user->email = $request->email;
	    	$user->phone = $request->phone;
	        $user->password = bcrypt($request->password);

	        $user->save();

    	} catch (\Exception $e)
    	{
    		return response()->json([
    			'status'	=>	false,
    			'message'	=>	'Failed to create the user account.',
    		], 500);
    	}
    	

        return response()->json([
        	'status' => true,
            'message' => 'User Account Created',
        ], 200);
    }

    /**
     * Refresh the token.
     * 
     * @return [type] [description]
     */
    public function refresh()
    {
    	return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the currently authenticated user.
     * 
     * @return [type] [description]
     */
    public function user()
    {
    	// return a resource in future.
    	return response()->json($this->guard()->user());
    }

    /**
     * LogOut the authenticated user.
     * 
     * @return [type] [description]
     */
    public function logout()
    {
    	try
    	{
    		$this->guard()->logout();

    	} catch (\Exception $e)
    	{
    		return response()->json([
    			'status'	=>	false,
    			'message'	=>	'Failed to logout the user',
    		], 500);
    	}

    	return response()->json([
    		'status'	=>	true,
    		'message'	=>	'Successfully logged out.',
    	], 200);
    }

    /**
     * Get the response with token array structure.
     * 
     * @param  [type] $token [description]
     * @return [type]        [description]
     */
    protected function respondWithToken($token)
    {
    	return response()
    			->json([
    				'token' => $token,
		    	], 200);
    }
}
