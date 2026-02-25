@extends('layout.index')

@section('title')
Pengaturan Pengguna
@endsection

@section('css')
<style>
    .settings-card {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
    }
    .settings-card .card-header {
        background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
        color: white;
        border: none;
    }
    .settings-card .card-header h5 {
        margin: 0;
        font-weight: 600;
    }
    .readonly-field {
        background-color: #f8f9fa;
        padding: 10px 12px;
        border-radius: 4px;
        border: 1px solid #e9ecef;
        color: #495057;
    }
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .toggle-slider {
        background-color: #0066cc;
    }
    input:checked + .toggle-slider:before {
        transform: translateX(26px);
    }
    .form-group label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }
    .btn-save {
        background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
        color: white;
        border: none;
        padding: 10px 30px;
        border-radius: 4px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 102, 204, 0.3);
    }
    .btn-save:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
</style>
@endsection

@section('content')

<!-- Breadcrumb row -->
<div class="row page-titles">
    <div class="col-md-5 col-12 align-self-center">
        <h3 class="text-themecolor mb-0">Pengaturan Pengguna</h3>
    </div>
    <div class="col-md-7 col-12 align-self-center d-none d-md-flex justify-content-end">
        <ol class="breadcrumb mb-0 p-0 bg-transparent">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item active">Pengaturan</li>
        </ol>
    </div>
</div>

<!-- Settings form -->
<div class="row">
    <div class="col-lg-8 col-md-10 col-12">
        <div class="card settings-card">
            <div class="card-header">
                <h5><i class="mdi mdi-settings mr-2"></i>Informasi dan Preferensi Akun</h5>
            </div>
            <div class="card-body">
                <form id="settings-form">
                    @csrf

                    <!-- Read-only fields section -->
                    <div class="row mb-4 pb-3 border-bottom">
                        <div class="col-12">
                            <h6 class="text-muted font-weight-600 mb-3">Informasi Akun (Tidak dapat diubah)</h6>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <div class="readonly-field">{{ $user->nama }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Username</label>
                                <div class="readonly-field">{{ $user->username }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NIP</label>
                                <div class="readonly-field">{{ $user->nip ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Unit Kerja</label>
                                <div class="readonly-field">{{ $unit_kerja_nama ?: '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Editable fields section -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted font-weight-600 mb-3">Preferensi Akun</h6>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="no_hp">Nomor Telepon / WhatsApp</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="no_hp" 
                                    name="no_hp" 
                                    value="{{ $user->no_hp ?: '' }}" 
                                    placeholder="Contoh: 08xxx xxxx xxxx"
                                >
                                <small class="text-muted d-block mt-2">Nomor ini digunakan untuk notifikasi WhatsApp terkait rapat.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_subscribe">Berlangganan Email Agenda Harian</label>
                                <div class="mt-2">
                                    <label class="toggle-switch">
                                        <input 
                                            type="checkbox" 
                                            id="is_subscribe" 
                                            name="is_subscribe"
                                            {{ $user->is_subscribe == 1 ? 'checked' : '' }}
                                        >
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span class="ml-3 text-muted" id="subscribe-status">
                                        {{ $user->is_subscribe == 1 ? 'Aktif - Anda akan menerima email agenda rapat setiap pagi.' : 'Nonaktif - Anda tidak akan menerima email agenda rapat.' }}
                                    </span>
                                </div>
                                <small class="text-muted d-block mt-2">Email agenda dikirimkan setiap hari pukul 06:00 ke alamat <strong>{{ session('username') }}@bps.go.id</strong>.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="row mt-4 pt-3 border-top">
                        <div class="col-12">
                            <button type="submit" class="btn-save" id="submit-btn">
                                <i class="fa fa-save mr-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
$(document).ready(function () {
    let token = document.head.querySelector('meta[name="_token"]').content,
        base_url = document.head.querySelector('meta[name="base_url"]').content;

    // Update subscribe status text on toggle
    $('#is_subscribe').on('change', function () {
        let status = $(this).is(':checked') 
            ? 'Aktif - Anda akan menerima email agenda rapat setiap pagi.'
            : 'Nonaktif - Anda tidak akan menerima email agenda rapat.';
        $('#subscribe-status').text(status);
    });

    // Form submission
    $('#settings-form').on('submit', function (e) {
        e.preventDefault();
        
        let no_hp = $('#no_hp').val().trim(),
            is_subscribe = $('#is_subscribe').is(':checked') ? 1 : 0;

        // Validation
        if (no_hp && !/^\d{10,}$/.test(no_hp.replace(/\D/g, ''))) {
            Swal.fire({
                type: 'error',
                title: 'Error!',
                html: '<p style="font-size:smaller">Nomor telepon harus terdiri dari minimal 10 digit angka.</p>'
            });
            return false;
        }

        // Disable submit button
        $('#submit-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i>Menyimpan...');

        // Send AJAX
        $.ajax({
            url: base_url + '/update-settings',
            dataType: 'json',
            type: 'POST',
            data: {
                _token: token,
                no_hp: no_hp,
                is_subscribe: is_subscribe
            },
            success: function (data) {
                Swal.fire({
                    type: 'success',
                    title: 'Sukses!',
                    html: '<p style="font-size:smaller">Pengaturan anda telah disimpan.</p>',
                    didClose: function () {
                        location.reload();
                    }
                });
            },
            error: function (err) {
                $('#submit-btn').prop('disabled', false).html('<i class="fa fa-save mr-2"></i>Simpan Perubahan');
                let errMsg = err.responseJSON?.message || err.responseText || 'Terjadi kesalahan saat menyimpan.';
                Swal.fire({
                    type: 'error',
                    title: 'Error!',
                    html: '<p style="font-size:smaller">' + errMsg + '</p>'
                });
            }
        });

        return false;
    });
});
</script>
@endsection
