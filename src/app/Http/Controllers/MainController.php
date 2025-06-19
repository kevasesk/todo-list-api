<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class MainController extends Controller
{
    public function index(): View
    {
        return view('index');
    }
    public function tasks(): View
    {
        return view('tasks');
    }
}
