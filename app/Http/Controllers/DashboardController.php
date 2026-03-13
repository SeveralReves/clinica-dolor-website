<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(Request $request)
    {   

        return view('dashboard');
    }
    public function users(Request $request)
    {   
        $users = User::all();

        return view('dashboard.users', compact('users'));
    }
}
