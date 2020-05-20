<?php
namespace App\Http\Controllers;

use App\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Index - Get user info
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return JsonResponse::success([
            'user' => auth()->user()
        ]);
    }
}