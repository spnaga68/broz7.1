<?php 
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App;
use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class AuthController
{
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email','password');
        try{
            if(!$token = JWTAuth::attempt($credentials)) //its failing on this line
            {
                return response()->json(['error' => 'invalid credentials'],401);
            }
        }
        catch(JWTException $e)
        {
            return response()->json(['error' => 'could not create token'],500);
        }
        return response()->json(compact('$token'));
    }
}?>
