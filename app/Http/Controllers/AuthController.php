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
     * Get user info
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return JsonResponse::success([
            'user' => auth()->user()
        ]);
    }

    /**
     * Handle refresh token
     *
     * @param \Illuminate\Http\Request $request
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