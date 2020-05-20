<?php
namespace App\Http\Controllers;

use App\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class AuthController extends Controller
{
    /**
     * Handle login
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return JsonResponse::error([], 'E-mail/password is wrong', 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Handle logout
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return JsonResponse::success([], 'Logged out');
    }

    /**
     * Handle refresh token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        if (!$token = auth()->refresh()) {
            return JsonResponse::error([], 'Could not generate new token', 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $expires = auth()->factory()->getTTL();

        return JsonResponse::success([
            'tokenType' => 'bearer',
            'expiresIn' => $expires * 60
        ])->withCookie(cookie(
            'token',
            $token,
            $expires,
            '/',
            config('session.domain'),
            config('session.secure'),
            true,
            false,
            Cookie::SAMESITE_STRICT
        ));
    }
}