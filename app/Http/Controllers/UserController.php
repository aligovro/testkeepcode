<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{

    public function index(): JsonResponse
    {
        //TODO убрать. для тестовой авторизации на фронте
        $users = User::all();
        return response()->json($users);
    }
}
