<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UnitKerjaModel;
use App\UserModel;
use Illuminate\Support\Facades\DB;

class UnitKerjaUserController extends Controller
{
    public function getIndex(Request $request)
    {
        if (session('level') != '2') {
            return redirect('/')->with('error', 'Akses ditolak');
        }

        $years = range(2020, date('Y') + 1);
        rsort($years);

        return view('pages.unit-kerja-users', compact('years'));
    }

    public function getUnitKerjaList(Request $request)
    {
        if (session('level') != '2') {
            echo json_encode(['result' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $year = $request->input('year', date('Y'));

        $subSql = '(SELECT COUNT(*) FROM unit_kerja_user '
                . 'INNER JOIN users ON users.id = unit_kerja_user.user_model_id '
                . 'WHERE unit_kerja_user.unit_kerja_model_id = unit_kerja.id '
                . 'AND unit_kerja_user.tahun = ?) as users_count';

        $units = UnitKerjaModel::select('unit_kerja.*')
            ->selectRaw($subSql, [$year])
            ->where('unit_kerja.tahun', $year)
            ->orderBy('unit_kerja.nama', 'asc')
            ->get();

        echo json_encode(['result' => 'sukses', 'data' => $units, 'year' => $year]);
    }

    public function postAddUnitKerja(Request $request)
    {
        if (session('level') != '2') {
            echo json_encode(['result' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $nama = $request->input('nama');
        $singkatan = $request->input('singkatan', '');
        $classBg = $request->input('class_bg', '');

        if (!$nama) {
            echo json_encode(['result' => 'error', 'message' => 'Nama unit kerja wajib diisi']);
            return;
        }

        $exists = UnitKerjaModel::where('nama', $nama)->where('tahun', $request->input('tahun', date('Y')))->exists();
        if ($exists) {
            echo json_encode(['result' => 'error', 'message' => 'Nama unit kerja sudah ada di tahun ini']);
            return;
        }

        $unit = UnitKerjaModel::create([
            'nama' => $nama,
            'singkatan' => $singkatan,
            'class_bg' => $classBg,
            'tahun' => $request->input('tahun', date('Y')),
        ]);

        echo json_encode(['result' => 'sukses', 'message' => 'Unit kerja berhasil ditambahkan', 'data' => $unit]);
    }

    public function postEditUnitKerja(Request $request)
    {
        if (session('level') != '2') {
            echo json_encode(['result' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $id = $request->input('id');
        $nama = $request->input('nama');
        $singkatan = $request->input('singkatan', '');
        $classBg = $request->input('class_bg', '');

        $unit = UnitKerjaModel::find($id);
        if (!$unit) {
            echo json_encode(['result' => 'error', 'message' => 'Unit kerja tidak ditemukan']);
            return;
        }

        $exists = UnitKerjaModel::where('nama', $nama)->where('id', '!=', $id)->exists();
        if ($exists) {
            echo json_encode(['result' => 'error', 'message' => 'Nama unit kerja sudah digunakan']);
            return;
        }

        $unit->nama = $nama;
        $unit->singkatan = $singkatan;
        $unit->class_bg = $classBg;
        $unit->save();

        echo json_encode(['result' => 'sukses', 'message' => 'Unit kerja berhasil diperbarui', 'data' => $unit]);
    }

    public function postDeleteUnitKerja(Request $request)
    {
        if (session('level') != '2') {
            echo json_encode(['result' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $id = $request->input('id');
        $unit = UnitKerjaModel::find($id);
        if (!$unit) {
            echo json_encode(['result' => 'error', 'message' => 'Unit kerja tidak ditemukan']);
            return;
        }

        DB::table('unit_kerja_user')->where('unit_kerja_model_id', $id)->delete();
        $unit->delete();

        echo json_encode(['result' => 'sukses', 'message' => 'Unit kerja berhasil dihapus']);
    }

    public function getUnitUsers(Request $request, $unitId)
    {
        if (session('level') != '2') {
            echo json_encode(['result' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $year = $request->input('year', date('Y'));
        $unit = UnitKerjaModel::find($unitId);

        if (!$unit) {
            echo json_encode(['result' => 'error', 'message' => 'Unit kerja tidak ditemukan']);
            return;
        }

        $users = $unit->users()
            ->wherePivot('tahun', $year)
            ->orderBy('nama')
            ->get();

        echo json_encode(['result' => 'sukses', 'data' => $users, 'unit' => $unit, 'year' => $year]);
    }

    public function getAvailableUsers(Request $request, $unitId)
    {
        if (session('level') != '2') {
            echo json_encode(['result' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $year = $request->input('year', date('Y'));
        $search = $request->input('search', '');

        $query = UserModel::whereDoesntHave('unitKerja', function ($q) use ($unitId, $year) {
            $q->where('unit_kerja_user.unit_kerja_model_id', $unitId)
              ->where('unit_kerja_user.tahun', $year);
        });

        if ($search) {
            $query = $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('nama')->get();

        echo json_encode(['result' => $users]);
    }

    public function postBulkAddUsers(Request $request)
    {
        if (session('level') != '2') {
            echo json_encode(['result' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $userIds = $request->input('user_ids', []);
        $unitId = $request->input('unit_id');
        $year = $request->input('year', date('Y'));

        if (empty($userIds) || !$unitId) {
            echo json_encode(['result' => 'error', 'message' => 'Data tidak lengkap']);
            return;
        }

        $added = 0;
        foreach ($userIds as $userId) {
            $exists = DB::table('unit_kerja_user')
                ->where('user_model_id', $userId)
                ->where('unit_kerja_model_id', $unitId)
                ->where('tahun', $year)
                ->exists();

            if (!$exists) {
                DB::table('unit_kerja_user')->insert([
                    'user_model_id' => $userId,
                    'unit_kerja_model_id' => $unitId,
                    'tahun' => $year,
                ]);
                $added++;
            }
        }

        echo json_encode(['result' => 'sukses', 'message' => "{$added} user berhasil ditambahkan"]);
    }

    public function postRemoveUser(Request $request)
    {
        if (session('level') != '2') {
            echo json_encode(['result' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $userId = $request->input('user_id');
        $unitId = $request->input('unit_id');
        $year = $request->input('year', date('Y'));

        if (!$userId || !$unitId) {
            echo json_encode(['result' => 'error', 'message' => 'Data tidak lengkap']);
            return;
        }

        $deleted = DB::table('unit_kerja_user')
            ->where('user_model_id', $userId)
            ->where('unit_kerja_model_id', $unitId)
            ->where('tahun', $year)
            ->delete();

        if ($deleted > 0) {
            echo json_encode(['result' => 'sukses', 'message' => 'User berhasil dihapus dari unit']);
        } else {
            echo json_encode(['result' => 'error', 'message' => 'Data tidak ditemukan']);
        }
    }

    public function postBulkRemoveUsers(Request $request)
    {
        if (session('level') != '2') {
            echo json_encode(['result' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $userIds = $request->input('user_ids', []);
        $unitId = $request->input('unit_id');
        $year = $request->input('year', date('Y'));

        if (empty($userIds) || !$unitId) {
            echo json_encode(['result' => 'error', 'message' => 'Data tidak lengkap']);
            return;
        }

        $deleted = DB::table('unit_kerja_user')
            ->whereIn('user_model_id', $userIds)
            ->where('unit_kerja_model_id', $unitId)
            ->where('tahun', $year)
            ->delete();

        echo json_encode(['result' => 'sukses', 'message' => "{$deleted} user berhasil dihapus"]);
    }
}
