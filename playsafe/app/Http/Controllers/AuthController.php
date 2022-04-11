<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\AccountDetails;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\MessageBag;
use Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = ['username' => $request->input('username'), 'password' => $request->input('password')];
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $accountDetails = AccountDetails::where('account_id', auth()->user()->account_id);
        return $accountDetails->first()->toJson();
    }

    public function register(Request $request) 
    {
        $existingUser = User::where('username', $request->input('username'))->first();
        $validation = Validator::make($request->all(), [
            'email' => 'email|required',
            'username' => 'required',
            'password' => 'required',
            'registeredRegion' => 'required',
            'subscriptionType' => 'required',
            'isContentProvider' => 'required'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors()->toArray(), 400);
        }
        
        if ($existingUser == null) {
            $accountDetails = AccountDetails::create([
                'registered_region' => $request->input('registeredRegion'),  
                'subscribtion_status' => $request->input('subscriptionType')
            ]);
            
            $accountDetails->refresh();
            User::create([
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'account_id' => $accountDetails->account_id,
                'is_content_provider' => $request->input('isContentProvider')
            ]);
            
            return response()->json(['registrationStatus' => 'Success'], 200);
        }
        return response()->json(['registrationStatus' => 'Registration failure as username already exists.'], 409);
    }
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}