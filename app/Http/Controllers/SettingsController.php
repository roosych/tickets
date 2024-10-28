<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function users()
    {
        $users = User::all();
        return view('cabinet.settings.users', compact('users'));
    }
}
