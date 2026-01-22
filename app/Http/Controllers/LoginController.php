<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserModel;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->data;
        $username = $data['username'];
        $password = md5($data['password']);
        $user = UserModel::select('users.*', 'unit_kerja.nama as nama_unit_kerja')->leftJoin('unit_kerja', 'unit_kerja.id', '=', 'users.unit_kerja')->where(['username' => $username, 'password' => $password])->first();
        $result = array();
        if($user)
        {
            if($user->is_active == 0) // akun user tidak aktif
            {
                $result['status'] = 'warning';
                $result['message'] = 'Akun anda sudah tidak aktif. Hubungi admin!';
            } else // akun aktif -> set session login
            {
                $session_user = array(
                    'username' => $user->username,
                    'nama' => $user->nama,
                    'nip' => $user->nip,
                    'no_hp' => $user->no_hp,
                    'user_id' => $user->id,
                    'unit_kerja' => $user->unit_kerja,
                    'nama_unit_kerja' => $user->nama_unit_kerja,
                    'level' => $user->level
                );
                session($session_user);
                $result['status'] = 'success';
                $result['message'] = 'Selamat Datang di MeetApp Kalsel, <br/><strong>'.$user->nama.'</strong>';                
            }
        } else // user tidak ditemukan
        {
            $result['status'] = 'error';
            $result['message'] = 'Username atau password anda salah!';
        }

        echo json_encode($result);
    }
}
