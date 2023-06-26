<?php

namespace silatng\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;


use silatng\Models\LoginLogModel;

class LoginController extends SilatNGController
{
    public function index()
    {
        $this->data['title'] = 'Login';
        
        $jenis_ikan = DB::select("select * from (SELECT trim(a.nama_jenis_ikan) as nama_jenis_ikan, SUM(LENGTH(trim(a.nama_jenis_ikan)) - LENGTH(REPLACE(trim(a.nama_jenis_ikan), ' ', '')) + 1) as words FROM db_master.mst_jenis_ikan a WHERE a.nama_jenis_ikan not like '%[%' and a.nama_jenis_ikan not like '%-%' and a.aktif = 'ya' and a.nama_jenis_ikan <> '' GROUP BY a.nama_jenis_ikan) a where a.words <= 2;");
        $captha_word = $jenis_ikan[rand(1, count($jenis_ikan))]->nama_jenis_ikan;
        $vals = array(
            'word' => $captha_word,
            'font_path' => base_path('resources/assets/fonts/texb.ttf'),
            'img_width' => '410',
            'img_height' => '60',
            'img_path' => base_path('resources/assets/images/captcha/'),
            'img_url' => \URL::asset('resources/assets/images/captcha').'/'
        );
        $captcha = $this->create_captcha($vals);
        $captcha['ip_address'] = \Request::ip();
        $data = array(
            'captcha_time' => $captcha['time'],
            'ip_address' => $captcha['ip_address'],
            'word' => $captcha['word']
        );
        $this->data['captcha'] = $captcha;
        Session::put('some_key', 'some_value');
        return view('modules.login.index', $this->data);
    }
    
    public function do_login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');
        $captchacode = $request->input('captchacode');
        $r = $request->input('r');

        // Login Log
        $loginLogData = new LoginLogModel;
        $loginLogData->user_name = $username;
        $loginLogData->ip_address = \Request::ip();
        $loginLogData->save();

        // $idlogin=$loginLogData->id;
        // dd($idlogin);
        if ($r !== null) {
            return Redirect($r);
        } else {
            $this->data['title'] = 'SIUP Migrasi PIT';
            Session::put('token', csrf_token());
            Session::put('some_key', 'some_value');
            Session::put('id',$loginLogData);
            return Redirect('/siup/migrasi-pit/')->with('session_data', session()->all());         }
    }
    //session 
    
    public function logout()
    {
        \Session::flush();
        // Auth::logout();
        return Redirect::route('login')->with('success', 'Anda telah berhasil Log Out!');
    }
}