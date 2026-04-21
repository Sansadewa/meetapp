@extends('layout.index')
@section('title') Manajemen Users per Unit Kerja @endsection

@section('css')
<link rel="stylesheet" type="text/css" href="{{ url('public/assets/datatable/dataTables.bootstrap4.css') }}">
<style>
    .unit-card {
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }
    .unit-card:hover {
        background-color: #f0f4ff;
        transform: translateX(3px);
    }
    .unit-card.active {
        background-color: #0066cc;
        color: white;
    }
    .unit-card.active .text-muted,
    .unit-card.active .unit-singkatan {
        color: rgba(255,255,255,0.8) !important;
    }
    .user-count-badge {
        background-color: #e8f0fe;
        color: #0066cc;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    .unit-card.active .user-count-badge {
        background-color: white;
        color: #0066cc;
    }
    .unit-actions {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0;
        transition: opacity 0.2s;
    }
    .unit-card:hover .unit-actions {
        opacity: 1;
    }
    .unit-actions .btn {
        padding: 2px 6px;
        font-size: 12px;
    }
    #unit-list {
        max-height: 500px;
        overflow-y: auto;
    }
</style>
@endsection

@section('content')
<div class="row page-titles">
    <div class="col-md-5 col-12 align-self-center">
        <h3 class="text-themecolor mb-0">Manajemen Users per Unit Kerja</h3>
    </div>
    <div class="col-md-7 col-12 align-self-center d-none d-md-flex justify-content-end">
        <ol class="breadcrumb mb-0 p-0 bg-transparent">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item active">Manajemen Unit Kerja</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="year-selector"><strong>Tahun</strong></label>
                            <select class="form-control" id="year-selector">
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><strong>Daftar Unit Kerja</strong></h5>
                            <button class="btn btn-success btn-sm" id="btn-add-unit">
                                <i class="mdi mdi-plus"></i> Tambah
                            </button>
                        </div>
                        <div class="list-group" id="unit-list">
                            <div class="text-center text-muted py-4" id="unit-loading">
                                <em>Pilih tahun terlebih dahulu...</em>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div id="users-panel" style="display:none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0"><strong id="selected-unit-name">Users</strong></h5>
                                <button class="btn btn-info btn-sm" id="btn-add-users">
                                    <i class="mdi mdi-plus"></i> Tambah Users
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table id="users-table" class="table table-striped table-bordered display" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th width="40"><input type="checkbox" id="select-all-users"></th>
                                            <th>Nama</th>
                                            <th>Username</th>
                                            <th>NIP</th>
                                            <th width="80">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div class="mt-2">
                                <button class="btn btn-danger btn-sm" id="btn-bulk-remove" disabled>
                                    <i class="mdi mdi-delete"></i> Hapus Dipilih (<span id="selected-count">0</span>)
                                </button>
                            </div>
                        </div>

                        <div id="welcome-panel" class="text-center py-5">
                            <i class="mdi mdi-account-group" style="font-size: 80px; color: #0066cc;"></i>
                            <h4 class="mt-3">Pilih Tahun & Unit Kerja</h4>
                            <p class="text-muted">Pilih tahun, lalu klik unit kerja di sebelah kiri untuk mengelola users.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add/Edit Unit Kerja -->
<div id="modal-unit-kerja" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modal-colored-header bg-success">
                <h4 class="modal-title text-white" id="modal-unit-title">Tambah Unit Kerja</h4>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-unit-id">
                <div class="form-group">
                    <label><strong>Nama Unit Kerja *</strong></label>
                    <input type="text" class="form-control" id="input-nama-unit" placeholder="Contoh: Bagian Tata Usaha" required>
                </div>
                <div class="form-group">
                    <label><strong>Singkatan</strong></label>
                    <input type="text" class="form-control" id="input-singkatan" placeholder="Contoh: BTU">
                </div>
                <div class="form-group">
                    <label><strong>Warna Label (class_bg)</strong></label>
                    <input type="text" class="form-control" id="input-class-bg" placeholder="Contoh: bg-info">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btn-save-unit">
                    <i class="mdi mdi-check"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Users -->
<div id="modal-add-users" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modal-colored-header bg-info">
                <h4 class="modal-title text-white">Tambah Users ke Unit Kerja</h4>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group" style="position:relative;">
                    <label><strong>Cari User</strong> <span class="badge badge-info" id="add-users-count">(Total: 0)</span></label>
                    <input type="text" class="form-control" id="users-search-input" placeholder="Ketik nama atau username..." autocomplete="off">
                    <div id="users-autocomplete" class="list-group" style="position:absolute; z-index:1000; max-height:200px; overflow-y:auto; display:none; width:calc(100% - 30px); margin-top:2px; border:1px solid #ddd; border-radius:4px;"></div>
                    <div id="users-display" class="mt-3" style="min-height:40px;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-info" id="btn-confirm-add-users">
                    <i class="mdi mdi-check"></i> Tambah Users
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ url('public/assets/datatable/jquery.dataTables.min.js') }}"></script>
<script src="{{ url('public/assets/datatable/custom-datatable.js') }}"></script>
<script src="{{ url('public/assets/block-ui/jquery.blockUI.js') }}"></script>
<script>
$(document).ready(function() {
    var token = document.head.querySelector('meta[name="_token"]').content,
        baseUrl = document.head.querySelector('meta[name="base_url"]').content,
        selectedUnitId = null,
        selectedYear = $('#year-selector').val(),
        usersTable = null;

    var loader = '<div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>';

    function ajaxPost(url, data, onSuccess) {
        data._token = token;
        $.ajax({
            url: baseUrl + url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(res) {
                if (res.result === 'sukses') {
                    if (onSuccess) onSuccess(res);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Gagal' });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
            }
        });
    }

    function loadUnitList() {
        selectedUnitId = null;
        $('#users-panel').hide();
        $('#welcome-panel').show();

        $.ajax({
            url: baseUrl + '/admin/unit-kerja-users/list',
            type: 'GET',
            data: { year: selectedYear },
            dataType: 'json',
            beforeSend: function() {
                $('#unit-list').html('<div class="text-center py-3"><em>Memuat...</em></div>');
            },
            success: function(res) {
                if (res.result !== 'sukses') return;
                var html = '';
                if (res.data.length === 0) {
                    html = '<div class="text-center text-muted py-4"><em>Belum ada unit kerja</em></div>';
                } else {
                    $.each(res.data, function(i, u) {
                        html += '<div class="list-group-item unit-card" data-unit-id="' + u.id + '">'
                            + '<div class="d-flex justify-content-between align-items-center pr-5">'
                            + '<div>'
                            + '<strong>' + u.nama + '</strong>'
                            + (u.singkatan ? ' <span class="unit-singkatan text-muted small">(' + u.singkatan + ')</span>' : '')
                            + '</div>'
                            + '<span class="user-count-badge">' + u.users_count + ' users</span>'
                            + '</div>'
                            + '<div class="unit-actions">'
                            + '<button class="btn btn-sm btn-outline-primary btn-edit-unit" data-id="' + u.id + '" data-nama="' + u.nama + '" data-singkatan="' + (u.singkatan || '') + '" data-class-bg="' + (u.class_bg || '') + '" title="Edit"><i class="mdi mdi-pencil"></i></button> '
                            + '<button class="btn btn-sm btn-outline-danger btn-delete-unit" data-id="' + u.id + '" data-nama="' + u.nama + '" title="Hapus"><i class="mdi mdi-delete"></i></button>'
                            + '</div>'
                            + '</div>';
                    });
                }
                $('#unit-list').html(html);
            },
            error: function() {
                $('#unit-list').html('<div class="text-center text-danger py-3"><em>Gagal memuat data</em></div>');
            }
        });
    }

    function loadUnitUsers(unitId) {
        if (usersTable) { usersTable.clear().draw(); }

        $.ajax({
            url: baseUrl + '/admin/unit-kerja-users/' + unitId + '/users',
            type: 'GET',
            data: { year: selectedYear },
            dataType: 'json',
            beforeSend: function() {
                $('#users-panel').block({ message: loader, overlayCSS: { backgroundColor: '#fff', opacity: 0.8, cursor: 'wait' } });
            },
            success: function(res) {
                $('#users-panel').unblock();
                if (res.result !== 'sukses') {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Gagal' });
                    return;
                }

                $('#selected-unit-name').text('Users di ' + res.unit.nama + ' (' + selectedYear + ')');
                $('#welcome-panel').hide();
                $('#users-panel').show();

                var rows = $.map(res.data, function(u) {
                    return {
                        id: u.id,
                        nama: u.nama,
                        username: u.username,
                        nip: u.nip || '-'
                    };
                });

                if (!usersTable) {
                    usersTable = $('#users-table').DataTable({
                        searching: false, paging: false, info: false,
                        data: rows,
                        columns: [
                            { data: null, render: function(d,t,r) { return '<input type="checkbox" class="user-checkbox" data-user-id="' + r.id + '">'; }, orderable: false },
                            { data: 'nama' },
                            { data: 'username' },
                            { data: 'nip' },
                            { data: null, render: function(d,t,r) { return '<button class="btn btn-danger btn-sm btn-remove-user" data-user-id="' + r.id + '" data-user-name="' + r.nama + '"><i class="mdi mdi-delete"></i></button>'; }, orderable: false }
                        ],
                        language: { emptyTable: 'Tidak ada user di unit ini untuk tahun ' + selectedYear }
                    });
                } else {
                    usersTable.clear().rows.add(rows).draw();
                }
                updateBulkBtn();
            },
            error: function() {
                $('#users-panel').unblock();
                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
            }
        });
    }

    var addedUsersList = [];
    var searchUsersTimeout = null;

    function updateAddedUsersDisplay() {
        var $display = $('#users-display');
        $display.empty();
        addedUsersList.forEach(function(u, i) {
            $display.append(
                '<span class="badge badge-info mr-2 mb-2 d-inline-flex align-items-center" style="font-size:0.9em; padding:0.5em 0.75em;">'
                + u.text
                + '<button type="button" class="close ml-2" onclick="removeAddedUser(' + i + ')" style="font-size:1.2em;">&times;</button>'
                + '</span>'
            );
        });
        $('#add-users-count').text('(Total: ' + addedUsersList.length + ')');
    }

    window.removeAddedUser = function(index) {
        addedUsersList.splice(index, 1);
        updateAddedUsersDisplay();
    };

    function initUsersSearch() {
        addedUsersList = [];
        updateAddedUsersDisplay();
        $('#users-search-input').val('');
        $('#users-autocomplete').hide().empty();

        $('#users-search-input').off('input').on('input', function() {
            var query = $(this).val().trim();
            var $ac = $('#users-autocomplete');
            clearTimeout(searchUsersTimeout);
            if (query.length < 2) { $ac.hide().empty(); return; }

            searchUsersTimeout = setTimeout(function() {
                $.ajax({
                    url: baseUrl + '/search-attendees',
                    dataType: 'json',
                    type: 'GET',
                    data: { q: query },
                    success: function(data) {
                        $ac.empty();
                        var filtered = data.filter(function(item) { return item.type === 'user'; });
                        if (filtered.length === 0) {
                            $ac.append('<div class="list-group-item text-muted">Tidak ada hasil</div>');
                        } else {
                            filtered.forEach(function(item) {
                                var isAdded = addedUsersList.some(function(a) { return a.id === item.id; });
                                if (!isAdded) {
                                    var $el = $('<a href="#" class="list-group-item list-group-item-action"></a>')
                                        .html(item.text)
                                        .data('item', item)
                                        .on('click', function(e) {
                                            e.preventDefault();
                                            addedUsersList.push({ id: item.id, text: item.text, userId: item.id.replace('user-', '') });
                                            updateAddedUsersDisplay();
                                            $('#users-search-input').val('');
                                            $ac.hide().empty();
                                        });
                                    $ac.append($el);
                                }
                            });
                        }
                        $ac.show();
                    }
                });
            }, 300);
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#users-search-input, #users-autocomplete').length) {
                $('#users-autocomplete').hide();
            }
        });
    }

    function updateBulkBtn() {
        var n = $('.user-checkbox:checked').length;
        $('#selected-count').text(n);
        $('#btn-bulk-remove').prop('disabled', n === 0);
    }

    function resetUnitModal() {
        $('#edit-unit-id').val('');
        $('#input-nama-unit').val('');
        $('#input-singkatan').val('');
        $('#input-class-bg').val('');
        $('#modal-unit-title').text('Tambah Unit Kerja');
    }

    // --- Year selector ---
    $('#year-selector').on('change', function() {
        selectedYear = $(this).val();
        loadUnitList();
    });

    // --- Unit click -> load users ---
    $(document).on('click', '.unit-card', function(e) {
        if ($(e.target).closest('.unit-actions').length) return;
        $('.unit-card').removeClass('active');
        $(this).addClass('active');
        selectedUnitId = $(this).data('unit-id');
        loadUnitUsers(selectedUnitId);
    });

    // --- Bulk checkbox ---
    $(document).on('change', '.user-checkbox', updateBulkBtn);
    $('#select-all-users').on('change', function() {
        $('.user-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkBtn();
    });

    // --- Add Unit Kerja ---
    $('#btn-add-unit').on('click', function() {
        resetUnitModal();
        $('#modal-unit-kerja').modal('show');
    });

    // --- Edit Unit Kerja ---
    $(document).on('click', '.btn-edit-unit', function(e) {
        e.stopPropagation();
        $('#edit-unit-id').val($(this).data('id'));
        $('#input-nama-unit').val($(this).data('nama'));
        $('#input-singkatan').val($(this).data('singkatan'));
        $('#input-class-bg').val($(this).data('class-bg'));
        $('#modal-unit-title').text('Edit Unit Kerja');
        $('#modal-unit-kerja').modal('show');
    });

    // --- Save Unit Kerja (Add/Edit) ---
    $('#btn-save-unit').on('click', function() {
        var id = $('#edit-unit-id').val();
        var nama = $('#input-nama-unit').val().trim();
        if (!nama) {
            Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Nama unit kerja wajib diisi' });
            return;
        }

        var payload = {
            nama: nama,
            singkatan: $('#input-singkatan').val().trim(),
            class_bg: $('#input-class-bg').val().trim(),
            tahun: selectedYear
        };

        if (id) {
            payload.id = id;
            ajaxPost('/admin/unit-kerja-users/update', payload, function(res) {
                $('#modal-unit-kerja').modal('hide');
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message });
                loadUnitList();
            });
        } else {
            ajaxPost('/admin/unit-kerja-users/store', payload, function(res) {
                $('#modal-unit-kerja').modal('hide');
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message });
                loadUnitList();
            });
        }
    });

    // --- Delete Unit Kerja ---
    $(document).on('click', '.btn-delete-unit', function(e) {
        e.stopPropagation();
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        Swal.fire({
            title: 'Hapus Unit Kerja?',
            html: 'Yakin ingin menghapus <strong>' + nama + '</strong>?<br>Semua relasi user di tahun manapun akan ikut terhapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(function(r) {
            if (r.isConfirmed) {
                ajaxPost('/admin/unit-kerja-users/delete', { id: id }, function(res) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message });
                    loadUnitList();
                });
            }
        });
    });

    // --- Add Users ---
    $('#btn-add-users').on('click', function() {
        if (!selectedUnitId) return;
        initUsersSearch();
        $('#modal-add-users').modal('show');
    });

    $('#btn-confirm-add-users').on('click', function() {
        var userIds = addedUsersList.map(function(u) { return u.userId; });
        if (userIds.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Pilih minimal satu user' });
            return;
        }
        ajaxPost('/admin/unit-kerja-users/bulk-add', {
            user_ids: userIds,
            unit_id: selectedUnitId,
            year: selectedYear
        }, function(res) {
            $('#modal-add-users').modal('hide');
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message });
            loadUnitUsers(selectedUnitId);
            loadUnitList();
        });
    });

    // --- Remove single user ---
    $(document).on('click', '.btn-remove-user', function() {
        var userId = $(this).data('user-id');
        var userName = $(this).data('user-name');
        Swal.fire({
            title: 'Hapus User?',
            text: 'Hapus ' + userName + ' dari unit kerja ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(function(r) {
            if (r.isConfirmed) {
                ajaxPost('/admin/unit-kerja-users/remove', {
                    user_id: userId,
                    unit_id: selectedUnitId,
                    year: selectedYear
                }, function(res) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message });
                    loadUnitUsers(selectedUnitId);
                    loadUnitList();
                });
            }
        });
    });

    // --- Bulk remove ---
    $('#btn-bulk-remove').on('click', function() {
        var userIds = $('.user-checkbox:checked').map(function() { return $(this).data('user-id'); }).get();
        if (userIds.length === 0) return;
        Swal.fire({
            title: 'Hapus Users?',
            text: 'Hapus ' + userIds.length + ' user dari unit kerja ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(function(r) {
            if (r.isConfirmed) {
                ajaxPost('/admin/unit-kerja-users/bulk-remove', {
                    user_ids: userIds,
                    unit_id: selectedUnitId,
                    year: selectedYear
                }, function(res) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message });
                    loadUnitUsers(selectedUnitId);
                    loadUnitList();
                });
            }
        });
    });

    // Auto-load on page ready
    loadUnitList();
});
</script>
@endsection
