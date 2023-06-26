<?php

namespace silatng\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session; 

use silatng\Models\ListNegaraModel;
use silatng\Models\ListProvinsiModel;
use silatng\Models\ListBadanHukumModel;
use App\Models\ListAgendaBaru;


class SIUPController extends SilatNGController
{
 
//Start Author Jefri
    public function siup_migrasi_pit()
    {
        // if ($id != session('id')){
        //     \Session::flush();
        //     // Auth::logout();
        //     return Redirect::route('login')->with('success', 'session ada telah habis');
        
        // }else{
        //     $this->data['title'] = 'SIUP Migrasi PIT';
        //     $sessionData = session('session_data');
    
        //     // dd(session('id'));
    
        //     return view('modules.siup.migrasi_pit.index', $this->data);    
        // }
        $this->data['title'] = 'SIUP Migrasi PIT';
        $sessionData = session('session_data');
        return view('modules.siup.migrasi_pit.index', $this->data);
    }

    public function siup_migrasi_pit_operation($operation, $id = null)
    {
        if ($id !== null) {
            $id = Crypt::decryptString($id);
        }
        switch ($operation) {
            case 'add':
                if ( (\Request::get('disclaimer') == 'agreed') && (\Request::get('token') == csrf_token()) ) {
                    $this->data['title'] = 'Add | SIUP Migrasi PIT';
                    $this->data['listNegara'] = ListNegaraModel::get();
                    $this->data['listProvinsi'] = ListProvinsiModel::get();
                    $this->data['listBadanHukum'] = ListBadanHukumModel::get();
                    $email = 'jovenbali86@gmail.com';
                    $results = DB::select('CALL sp_mig_nomor_agenda_baru(1, ?)', [$email]);
                    $this->data['sp_mig_nomor_agenda_baru'] = $results;
                    $this->data['sp_mig_status_izin'] = DB::select('CALL sp_mig_status_izin(1)');
                    $this->data['sp_mig_jenis_izin'] = DB::select('CALL sp_mig_jenis_izin(1)');
                   
                    // START EDIT JO //
                    $this->data['sp_mig_split_alokasi'] = DB::select('CALL sp_mig_split_alokasi(1779)');
                    
                    // $result = DB::select('SELECT * FROM your_table WHERE id_pemilik = ?', [$idPemilik]);
                    // $this->data['sp_mig_jenis_izin'] = $result;
                    // END EDIT JO // 
                    
                    // dd($this->data['sp_mig_nomor_agenda_baru']);
                    return view('modules.siup.migrasi_pit.add', $this->data);
                    break;
                } else {
                    abort(404);
                    break;
                }
            default:
                abort(404);
                break;
        }
    }

    public function agendaControlller()
    {
        $model = new ListAgendaBaru();
        $no_agenda_baru = $model->getStoredProcValue();
        $nomor_nib = $model->getStoredProcValue();

    return view('modules.siup.migrasi_pit.add', $this->data);
    }


}

//End Author Jefri