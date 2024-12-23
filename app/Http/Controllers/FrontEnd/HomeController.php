<?php

namespace App\Http\Controllers\FrontEnd;
use App\Http\Controllers\Controller;
use App\Models\Home;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $title = "TARASVAT Industrial Electronics";
        return view('frontend.home.index', compact('title'));
    }
}
