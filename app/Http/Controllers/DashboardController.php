<?php

namespace silatng\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

// use silatng\Http\Models\KeuanganKasModel;

class DashboardController extends SilatNGController
{
    public function index()
    {
        $this->data['title'] = 'Dashboard';
        
        // Menambahkan beberapa kunci dan nilai ke dalam sesi
        Session::put('some_key', 'some_value');
        Session::put('another_key', 'another_value');
        
        return view('modules.dashboard.index', $this->data);
    }
}

