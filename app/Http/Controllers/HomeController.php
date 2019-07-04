<?php

namespace App\Http\Controllers;

use App\Emisor;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $numEmisores=Emisor::count();
        return view('/inicio')->with(['modulo'=>'Inicio','num_emisores'=>$numEmisores]);
    }
}
