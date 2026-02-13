<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // Return users for manager selection
        // Maybe filter by role? For now return all.
        return response()->json(User::select('id', 'username', 'role')->get());
    }
}
