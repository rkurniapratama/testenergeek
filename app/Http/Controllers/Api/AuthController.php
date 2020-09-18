<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\User;
use DB;
use Hash;

class AuthController extends Controller
{
/**
 * @OA\Post(
 * path="/api/auth/login",
 * description="Login",
 * operationId="auth_login",
 * tags={"Auth"},
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"username","password"},
 *              @OA\Property(property="username", type="string"),
 *              @OA\Property(property="password", type="string")
 *          ),
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Success",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *          )
 *      )
 * )
 */
    public function login(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'password' => 'required'
            ]);
    
            if($validator->fails()){
                return response()->json([
                    'status' => 'E', 
                    'message' => 'error_validation', 
                    'errors' => $validator->errors()],
                     400
                    );
            }

            $credentials = $request->only('username', 'password');

            if (!$token = auth()->attempt($credentials)) {
                throw new \Exception('invalid_credentials');
            }
            else {
                return response()->json([
                    'status' => 'S', 
                    'message' => 'successfully_logged_in', 
                    'token' => $this->respondWithToken($token)]
                );
            }
        } 
        catch (\Exception $ex) {
            return response()->json([
                'status' => 'E', 
                'message' => $ex->getMessage()], 
                500
            );
        }
    }

/**
 * @OA\Get(
 * path="/api/auth/logout",
 * description="Logout",
 * operationId="auth_logout",
 * tags={"Auth"},
 * security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Success",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *          )
 *      )
 * )
 */
    public function logout()
    {
        auth()->logout(true);

        return response()->json([
            'status' => 'S', 
            'message' => 'successfully_logged_out']
        );
    }
 
/**
 * @OA\Post(
 * path="/api/auth/register",
 * description="Register",
 * operationId="auth_register",
 * tags={"Auth"},
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"name","username","password","password_confirmation","email","no_hp","address","desc","role"},
 *              @OA\Property(property="name", type="string"),
 *              @OA\Property(property="username", type="string"),
 *              @OA\Property(property="password", type="string"),
 *              @OA\Property(property="password_confirmation", type="string"),
 *              @OA\Property(property="email", type="string"),
 *              @OA\Property(property="no_hp", type="string"),
 *              @OA\Property(property="address", type="string"),
 *              @OA\Property(property="desc", type="string"),
 *              @OA\Property(property="role", type="integer")
 *          ),
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Success",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *          )
 *      )
 * )
 */
    public function register(Request $request)
    {
        try
        {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'username' => 'required|unique:users',
                'password' => 'required|confirmed',
                'email' => 'required|email|unique:users',
                'no_hp' => 'required|unique:users',
                'address' => 'required',
                'role' => 'required|numeric'
            ]);
    
            if($validator->fails()){
                return response()->json([
                    'status' => 'E', 
                    'message' => 'error_validation', 
                    'errors' => $validator->errors()],
                     400
                    );
            }
     
            $user = new User();
            $user->name = $request->name;
            $user->username = $request->username;
            $user->password = Hash::make($request->password);
            $user->email = $request->email;
            $user->no_hp = $request->no_hp;
            $user->address = $request->address;
            $user->desc = $request->desc;
            $user->role = $request->role;
            $user->save();
    
             DB::commit();

            $token = auth()->login($user);
            return response()->json([
                'status' => 'S', 
                'message' => 'successfully_registered', 
                'user' => $user, 
                'token' => $this->respondWithToken($token)]
            );
        } 
        catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                'status' => 'E', 
                'message' => $ex->getMessage()], 
                500
            );
        }
    }
 
/**
 * @OA\Get(
 * path="/api/auth/profile",
 * description="Profile",
 * operationId="auth_profile",
 * tags={"Auth"},
 * security={{"bearerAuth":{}}},
 *      @OA\Response(
 *          response=200,
 *          description="Success",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *          )
 *      )
 * )
 */
    public function profile()
    {
        try 
        {
            if (!$user = auth()->user()) {
                throw new \Exception('user_not_found');
            }
            else {
                return response()->json([
                    'status' => 'S', 
                    'message' => 'successfully_get_user',
                    'user' => $user]
                );
            }
        } 
        catch (\Exception $ex) {
            return response()->json([
                'status' => 'E', 
                'message' => $ex->getMessage()], 
                500
            );
        }
    }

    private function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60
        ];
    }
}
