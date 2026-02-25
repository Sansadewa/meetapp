<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserModel;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Get the user settings page
     */
    public function getSettingsPage(Request $request)
    {
        $userId = session('user_id');
        $user = UserModel::find($userId);

        if (!$user) {
            abort(404);
        }

        // Get user's unit kerja name
        $unitKerjaId = $user->unit_kerja;
        $unitKerjaName = '';
        if ($unitKerjaId) {
            $unitKerja = DB::table('unit_kerja')->where('id', $unitKerjaId)->first();
            if ($unitKerja) {
                $unitKerjaName = $unitKerja->nama;
            }
        }

        return view('pages.settings', [
            'user' => $user,
            'unit_kerja_nama' => $unitKerjaName
        ]);
    }

    /**
     * Update user settings (phone number and email subscription)
     */
    public function updateSettings(Request $request)
    {
        $data = $request->data ?: $request->all();

        $userId = session('user_id');
        $user = UserModel::find($userId);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['result' => 'error', 'message' => 'User not found']);
            exit;
        }

        // Validate inputs
        $no_hp = isset($data['no_hp']) ? trim($data['no_hp']) : '';
        $is_subscribe = isset($data['is_subscribe']) ? (int) $data['is_subscribe'] : 0;

        // Validate phone number: if provided, must be at least 10 digits
        if (!empty($no_hp)) {
            $digitsOnly = preg_replace('/\D/', '', $no_hp);
            if (strlen($digitsOnly) < 10) {
                http_response_code(422);
                echo json_encode(['result' => 'error', 'message' => 'Nomor telepon harus terdiri dari minimal 10 digit angka.']);
                exit;
            }
            // Store the phone number with or without formatting as provided
            $user->no_hp = $no_hp;
        }

        // Update subscription preference
        $user->is_subscribe = $is_subscribe;

        // Save changes
        $user->save();

        // Update session with new phone number
        session(['no_hp' => $user->no_hp]);

        http_response_code(200);
        echo json_encode(['result' => 'success', 'message' => 'Pengaturan telah disimpan.']);
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $data = $request->data ?: $request->all();

        $userId = session('user_id');
        $user = UserModel::find($userId);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['result' => 'error', 'message' => 'User not found']);
            exit;
        }

        // Get input
        $passwordOld = isset($data['password_old']) ? $data['password_old'] : '';
        $passwordNew = isset($data['password_new']) ? $data['password_new'] : '';

        // Validate inputs
        if (empty($passwordOld) || empty($passwordNew)) {
            http_response_code(422);
            echo json_encode(['result' => 'error', 'message' => 'Password lama dan password baru harus diisi.']);
            exit;
        }

        if (strlen($passwordNew) < 6) {
            http_response_code(422);
            echo json_encode(['result' => 'error', 'message' => 'Password baru harus minimal 6 karakter.']);
            exit;
        }

        // Verify current password (MD5 hash - matching existing login system)
        $oldPasswordHash = md5($passwordOld);
        if ($oldPasswordHash !== $user->password) {
            http_response_code(401);
            echo json_encode(['result' => 'error', 'message' => 'Password lama anda tidak sesuai.']);
            exit;
        }

        // Hash new password (MD5 - consistent with existing auth)
        $newPasswordHash = md5($passwordNew);

        // Prevent same password
        if ($newPasswordHash === $user->password) {
            http_response_code(422);
            echo json_encode(['result' => 'error', 'message' => 'Password baru harus berbeda dengan password lama.']);
            exit;
        }

        // Update password
        $user->password = $newPasswordHash;
        $user->save();

        http_response_code(200);
        echo json_encode(['result' => 'success', 'message' => 'Password telah diubah.']);
    }
}
