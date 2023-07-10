<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountInfoController extends Controller
{
    public function account_info()
    {
        return Inertia::render('AccountInfo/AccountInfo');
    }
}
