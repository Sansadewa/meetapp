@extends('layout.index')
@section('title') Daftar Rapat @endsection

@section('css')
<link rel="stylesheet" type="text/css" href="{{ url('public/assets/datatable/dataTables.bootstrap4.css') }}">
<style>
</style>
@endsection

@section('content')
<!-- ============================================================== -->
<!-- Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
<div class="row page-titles">
    <div class="col-md-5 col-12 align-self-center">
        <h3 class="text-themecolor mb-0">Daftar Rapat</h3>
    </div>
    <div class="col-md-7 col-12 align-self-center d-none d-md-flex justify-content-end">
        <ol class="breadcrumb mb-0 p-0 bg-transparent">
            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
            <li class="breadcrumb-item active">Daftar Rapat</li>
        </ol>
    </div>
</div>
<!-- ============================================================== -->
<!-- End Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title"><span class="lstick d-inline-block align-middle"></span> Daftar Rapat</h4>
                <h6 class="card-subtitle">Ket: 
                    <ol>
                        <li>
                        Upload dokumentasi (notulensi, foto kegiatan, daftar hadir, dsbnya) untuk setiap kegiatan (rapat, pelatihan, dsb) dengan menekan tombol upload <br> <a href="javascript: void(0)" class="btn btn-warning btn-sm mt-2 mb-2"><i class="ti ti-upload"></i> Dokumentasi</a> <br>
                        Anda bisa mengupload seluruh dokumentasi untuk setiap kegiatan satu per satu melalui tombol upload tersebut.
                        </li>
                        <li class="mt-2">
                        Download / Unduh seluruh dokumentasi (notulensi, foto kegiatan, daftar hadir, dsbnya) kegiatan yang sudah pernah anda upload dengan menekan tombol download <br><a href="javascript: void(0)" class="btn btn-primary btn-sm mt-2 mb-2"><i class="ti ti-download"></i> Dokumentasi</a> 
                        </li>
                        <li class="mt-2">
                        Hapus seluruh dokumentasi (notulensi, foto kegiatan, daftar hadir, dsbnya) kegiatan yang sudah pernah anda upload dengan menekan tombol hapus <br><a href="javascript: void(0)" class="btn btn-danger btn-sm mt-2 mb-2"><i class="ti ti-close"></i> Dokumentasi</a> 
                        </li>
                        <li class="mt-2">
                        Gunakan tombol zoom pada masing-masing kegiatan <br> <a href="javascript: void(0)" class="btn btn-outline-info btn-sm mt-2 mb-2"><i class="ti ti-video-camera"></i> Zoom</a> <br> untuk melihat ID Meeting, Password, dan Link Zoom Meeting yang sudah dialokasikan oleh administrator
                        </li>
                    </ol>
                </h6>
                <hr>
                <div class="form-group">
                    <h4><label class="control-label">Pilihan Tahun</label></h4>
                    <select class="form-control" name="tahun" id="tahun" required="">
                        
                        @for ($year = 2020; $year <= date('Y'); $year++)
                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="table-responsive mt-5">
                    <table id="file_export" class="table table-striped table-bordered display">
                        <thead>
                            <tr>
                                <th>Tanggal Mulai Rapat</th>
                                <th width="20%">Waktu</th>
                                <th width="25%">Nama Rapat</th>
                                <th width="25%">Topik Rapat</th>
                                <th width="15%">Unit Kerja</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Tanggal Mulai Rapat</th>
                                <th>Waktu</th>
                                <th>Nama Rapat</th>
                                <th>Topik Rapat</th>
                                <th>Unit Kerja</th>
                                <th>Status</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Info Header Modal -->
<div id="modal-zoom" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="info-header-modalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modal-colored-header bg-info">
                <h4 class="modal-title text-white" id="info-header-modalLabel">Detail Zoom Meeting</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="spinner" id="loader-modal">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                    <div class="bounce3"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endsection

@section('js')
<script src="{{ url('public/assets/moment/moment.min.js')}}"></script>
<script src="{{ url('public/assets/datatable/jquery.dataTables.min.js')}}"></script>
<script src="{{ url('public/assets/datatable/custom-datatable.js')}}"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script>
<script>
    $(document).ready(function () {
        let token = document.head.querySelector('meta[name="_token"]').content,
            base_url = document.head.querySelector('meta[name="base_url"]').content,
            loader = `
                <div class="spinner">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                    <div class="bounce3"></div>
                </div>
            `;

        var tabel_rapat = $('#file_export').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            responsive: true,
            autoWidth: false,
            processing: true,
            ajax: {
                url: base_url + '/get-rapat',
                dataSrc: function (data) {
                    data.forEach((item, index) => {

                        data[index].nama_rapat = (item.use_zoom == '1' ?
                            `${item.nama}<br/><button data-zoom="${item.id}" data-toggle="tooltip" data-placement="bottom" title="Detail Zoom Meeting" class="btn btn-outline-info btn-sm zoom-id mt-2"><i class="ti ti-video-camera"></i> Zoom</button>` :
                            `${item.nama}`);
                        data[index].waktu_rapat = (moment(item.tanggal_rapat_start).isSame(
                                    moment(item.tanggal_rapat_end)) ? moment(item
                                    .tanggal_rapat_start).format("D MMM YYYY") : moment(item
                                    .tanggal_rapat_start).format("D MMM YYYY") + ' - ' +
                                moment(item.tanggal_rapat_end).format("D MMM YYYY")) +
                            '<br/>' + item.waktu_mulai_rapat + ' s.d ' + item
                            .waktu_selesai_rapat + ' WITA';
                        data[index].action =
                            `
                        <a href="${base_url}/meeting/${item.uid}" target="_blank" class="btn btn-success btn-sm mt-2 detail-rapat" data-toggle="tooltip" data-placement="right" title="Detail Rapat"><i class=" fa fa-info-circle"></i> Detail Rapat</a> <br/> <br/> 
                        ${item.is_notulensi == '1' ? '<button data-rapat = "'+item.id+'" class="btn btn-primary btn-sm download-notulensi" data-toggle="tooltip" data-placement="right" title="Download Dokumentasi"><i class="ti ti-download"></i> Dokumentasi</button> <br/>' : '<span style="background-color: #a0f0f4;padding: 5px;border-radius: 5px;color: black; font-size:small"><i class="ti ti-notepad"></i> Dokumentasi belum tersedia</span> <br/>'} 
                        <button data-rapat = "${item.id}" class="btn btn-warning btn-sm mt-2 upload-notulensi" data-toggle="tooltip" data-placement="right" title="Upload Dokumentasi"><i class="ti ti-upload"></i> Dokumentasi</button> <br/> 
                        ${item.is_notulensi == '1' ? '<button data-rapat = "'+item.id+'" class="btn btn-danger btn-sm mt-2 hapus-notulensi" data-toggle="tooltip" data-placement="right" title="Hapus Dokumentasi"><i class="ti ti-close"></i> Dokumentasi</button>' : ''}`;
                    });
                    return data;
                }
            },
            language: {
                processing: `<div class="spinner">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                    <div class="bounce3"></div>
                </div>`
            },
            columns: [{
                    data: 'tanggal_rapat_start'
                },
                {
                    data: 'waktu_rapat'
                },
                {
                    data: 'nama_rapat'
                },
                {
                    data: 'topik'
                },
                {
                    data: 'nama_unit_kerja'
                },
                {
                    data: 'action',
                }
            ],
            columnDefs: [{
                    orderData: 0,
                    targets: 1
                },
                {
                    visible: false,
                    targets: 0
                }
            ],
            order: [
                [0, "desc"]
            ],
            drawCallback: function () {
                $('[data-toggle="tooltip"]').tooltip()
            }
        });
        $('.buttons-copy, .buttons-csv, .buttons-print, .buttons-pdf, .buttons-excel').addClass(
            'btn btn-primary mr-1');

        $("#file_export tbody").on('click', '.zoom-id', function () {
            $("#modal-zoom").modal('toggle');
            $.ajax({
                url: base_url + '/get-detail-zoom',
                dataType: 'json',
                type: 'POST',
                data: {
                    _token: token,
                    data: $(this).data('zoom')
                },
                success: function (data) {
                    let detail_rapat;
                    if (data.result ==
                        'gagal') // data rapat tidak ditemukan, mungkin sudah dihapus ....
                    {
                        detail_rapat = `
                    <div class="alert alert-danger" role="alert">
                        <i class="dripicons-wrong mr-2"></i> Data rapat tidak ditemukan!                        
                    </div>
                   `;
                    } else // data rapat ditemukan
                    {
                        let zoom_data = '';
                        data.data.forEach((temp, index, arr) => {
                            zoom_data += `Tanggal: <strong>${data.data[index].tanggal_zoom ? moment(data.data[index].tanggal_zoom).format('D MMM YYYY') : '-'} </strong> <br/>
                                      Zoom ID:<strong> <span id="z-id_temp${data.data[index].zoom}">${data.data[index].zoom_id ? data.data[index].zoom_id + '</span> <button data-zoom="'+data.data[index].zoom+'" data-val="'+data.data[0].zoom_id+'" data-stat="z-id" style="border: 0px" class="btn btn-outline-success btn-sm edit-zoom" data-toggle="tooltip" data-placement="top" title="Click to Edit"><i class="ti ti-pencil-alt"></i></button>' : '-'}</strong>  <br/>
                                      Password: <strong><span id="z-pw_temp${data.data[index].zoom}">${data.data[index].zoom_password ? data.data[index].zoom_password + '</span> <button data-zoom="'+data.data[index].zoom+'" data-val="'+data.data[0].zoom_password+'" style="border: 0px" data-stat="z-pw" class="btn btn-outline-success btn-sm edit-zoom" data-toggle="tooltip" data-placement="top" title="Click to Edit"><i class="ti ti-pencil-alt"></i></button>' : '-'}</strong><br/>
                                      Link: <strong><span id="z-link_temp${data.data[index].zoom}">${data.data[index].zoom_id ? (data.data[index].zoom_link ? '<a target="_blank" href="'+data.data[index].zoom_link+'">'+data.data[index].zoom_link+'</a>' : '-') + '</span> <button data-zoom="'+data.data[index].zoom+'" style="border: 0px" data-val="'+data.data[0].zoom_link+'" data-stat="z-link" class="btn btn-outline-success btn-sm edit-zoom" data-toggle="tooltip" data-placement="top" title="Click to Edit"><i class="ti ti-pencil-alt"></i></button>'  : '-'} </strong> <br/>
                                      Host: <strong><span id="z-host_temp${data.data[index].zoom}">${data.data[index].zoom_id ? data.data[index].nama_host +'</span> <button data-zoom="'+data.data[index].zoom+'" data-val="'+data.data[0].host+'" style="border: 0px" data-stat="z-host" class="btn btn-outline-success btn-sm edit-host" data-toggle="tooltip" data-placement="top" title="Click to Edit"><i class="ti ti-pencil-alt"></i></button>' : '-' }</strong>                                    
                                    `;
                            if(data.u_lv == '2')
                            {
                                zoom_data += data.data[index].zoom_id ?
                                `<br/><button data-zoom="${data.data[index].zoom}" class="btn btn-info btn-sm kirim-notif" data-toggle="tooltip" data-placement="right" title="Kirim Notif WA"><i class="ti ti-bell"></i> Notif WA</button>` :
                                '';
                            }
                            
                            if (index != arr.length - 1) {
                                zoom_data += '<hr/>';
                            }
                        });
                        detail_rapat = `<h5 class="mt-0" style="text-align:center; font-weight: bold; text-transform: uppercase">${data.data[0].nama}</h5>
                        <p><strong>Topik Rapat</strong> <br/>${data.data[0].topik}</p>
                        <p><strong>Latar Belakang Rapat</strong> <br/>${data.data[0].latar_belakang ? data.data[0].latar_belakang : '-'}</p>
                        <p><strong>Zoom</strong><br/>
                            ${zoom_data}
                        </p>
                    `;
                    }
                    $('.modal-body').html(detail_rapat);

                },
                error: function (err) {
                    Swal.fire({
                        type: 'error',
                        title: 'Error!',
                        html: '<p style="font-size: smaller">Terdapat kesalahan pada jaringan anda. Refresh halaman ini ....</p>'
                    }).then(function () {
                        location.reload();
                    })
                }
            })
        });
        $("#modal-zoom").on('hidden.bs.modal', function () {
            $('.modal-body').html(`<div class="spinner" id="loader-modal">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                    <div class="bounce3"></div>
                </div>`);
        });

        $("#modal-zoom").on('shown.bs.modal', function () {
            $('[data-toggle="tooltip"]').tooltip();
            $(document).off('focusin.modal');
        })

        $("#file_export tbody").on('click', '.upload-notulensi', function () {
            $(this).blur();
            let rapat = $(this).data('rapat'),
                data_row = tabel_rapat.row($(this).parents('tr')).data();
            Swal.fire({
                title: '<h2>UPLOAD NOTULEN RAPAT</h2>',
                html: `
                <p style="font-size: smaller; text-transform: uppercase; margin-bottom: 25px; margin-top: 5px;"><strong>${data_row.nama}</strong></p>
                <div class="form-group row" style="margin-bottom: 0px">
                    <label class="col-sm-2 form-control-label" style="font-size: small">Pilih File Notulensi: <span class="tx-danger">*</span></label>
                    <div class="col-sm-10 mg-t-10 mg-sm-t-0">
                        <input id="file_notulensi" name="file_notulensi" type="file" class="form-control" accept=".docx, .pdf, .doc" />
                    </div>                                            
                </div>
            `,
                confirmButtonText: 'Upload Notulensi',
                showCancelButton: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    let form_notulensi = new FormData();
                    form_notulensi.append('notulensi', $("#file_notulensi")[0].files[0]);
                    form_notulensi.append('_token', token);
                    form_notulensi.append('rapat', rapat);
                    if (form_notulensi.get('notulensi') == 'undefined' || form_notulensi
                        .get('notulensi').size == 0) {
                        Swal.showValidationMessage(
                            'Silahkan pilih file yang akan diupload terlebih dahulu. Terima kasih :)'
                        );
                    } else {
                        return new Promise((resolve, reject) => {
                            $.ajax({
                                url: base_url + '/upload-notulensi',
                                type: 'POST',
                                dataType: 'json',
                                data: form_notulensi,
                                contentType: false,
                                cache: false,
                                processData: false,
                                success: function (data) {
                                    resolve(data);
                                },
                                error: function (data) {
                                    reject(data);
                                }
                            })
                        }).then((success) => {
                            if (success.status == 'gagal') {
                                Swal.showValidationMessage(
                                    success.message
                                );
                                return false;
                            }
                            tabel_rapat.ajax.reload(null, false);
                            return {
                                status: 'success'
                            };
                        }).catch((error) => {
                            // console.log(error);
                            return {
                                status: 'error'
                            };
                        });

                    }

                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then(function (val) {
                if (val.value) {
                    if (val.value.status == 'error') {
                        Swal.fire({
                            type: 'error',
                            title: 'Error!',
                            html: '<p style="font-size:smaller">Terdapat kesalahan pada jaringan anda. Refresh halaman ini....</p>'
                        }).then(function () {
                            location.reload();
                        })
                    } else {
                        Swal.fire({
                            type: 'success',
                            title: 'Sukses!!',
                            html: '<p syle="font-size: smaller">Berhasil upload notulensi rapat!</p>'
                        })
                    }
                }

            })
        });

        $("#file_export tbody").on('click', '.hapus-notulensi', function () {
            $(this).blur();
            let rapat = $(this).data('rapat'),
                data_row = tabel_rapat.row($(this).parents('tr')).data();
            Swal.fire({
                type: 'question',
                title: 'Konfirmasi Hapus!',
                html: `<p style="font-size: smaller">Apakah anda yakin akan menghapus file notulensi untuk rapat: <br/><br/>
                    <span style="text-transform: uppercase"><strong>${data_row.nama}</strong></span>
                </p>`,
                confirmButtonText: 'Ya, Hapus Notulensi',
                showCancelButton: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: base_url + '/hapus-notulensi',
                            dataType: 'json',
                            type: 'POST',
                            data: {
                                _token: token,
                                rapat: rapat
                            },
                            success: function (data) {
                                resolve(data);
                            },
                            error: function (err) {
                                reject(err);
                            }
                        })
                    }).then((suc) => {
                        tabel_rapat.ajax.reload(null, false);
                        return {
                            status: 'sukses'
                        };
                    }).catch((err) => {
                        return {
                            status: 'gagal'
                        };
                    })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then(function (val) {
                if (val.value) {
                    if (val.value.status == 'gagal') {
                        Swal.fire({
                            type: 'error',
                            title: 'Error!',
                            html: '<p style="font-size:smaller">Terdapat kesalahan pada jaringan anda. Refresh halaman ini....</p>'
                        }).then(function () {
                            location.reload();
                        })
                    } else {
                        Swal.fire({
                            type: 'success',
                            title: 'Sukses!!',
                            html: '<p syle="font-size: smaller">Berhasil menghapus notulensi rapat!</p>'
                        })
                    }
                }
            })
        });

        $("#file_export tbody").on('click', '.download-notulensi', function () {
            $(this).blur();
            let rapat = $(this).data('rapat');
            location.href = `${base_url}/download-notulensi?rapat=${rapat}`;
        });
        
        $('body').on('click', '.edit-host', function(){
            $(this).blur();
            let zoom = $(this).data('zoom'),
                val = $(this).data('val'),
                stat = $(this).data('stat');

            Swal.fire({
                type: 'question',
                html: `<div class="loading-temp">${loader}</div>`,
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan Perubahan',
                showLoaderOnConfirm: true,
                onOpen: () => {
                    $.ajax({
                        url: base_url + '/get-zoom-host',
                        dataType: 'json',
                        type: 'POST',
                        data: {
                            _token: token,
                            data: zoom
                        },
                        success: function (data) {
                            if (data.status == 'forbidden') {
                                Swal.fire({
                                    type: 'error',
                                    title: 'Wadooooh!',
                                    html: '<p>Hanya admin yang bisa merubah segalanyaaa~</p>'
                                });
                                return false;
                            }

                            let option = `<option value="-">-- Pilih Host Zoom Meeting --</option>`;
                            for(let i=0; i<data.host.length; i++)
                            {
                                option += `<option ${data.zoom.host == data.host[i].id ? 'selected' : ''} value="${data.host[i].id}">${data.host[i].nama}</option>`;
                            }

                            // let val_temp = stat == 'z-id' ? data.result
                            //     .zoom_id : (stat == 'z-pw' ? data.result
                            //         .zoom_password : data.result.zoom_link);
                            $('.loading-temp').html(`
                            <p style="font-size: ; text-transform: uppercase"><strong>Pilih Host Zoom Meeting</strong></p>
                            <select style="margin-bottom: 15px" class="form-control edit-host-field">
                            ${option}
                            </select>
                            
                            `);
                        },
                        error: function (err) {
                            Swal.fire({
                                type: 'error',
                                title: 'Error!',
                                html: '<p style="font-size: smaller">Terdapat kesalahan pada jaringan anda. Refresh halaman ini</p>'
                            }).then(function () {
                                location.reload();
                            })
                        }
                    })
                },
                preConfirm: () => {
                    let edit_temp = $("body .edit-host-field").val();
                    let edit_temp_text = $("body .edit-host-field option:selected").text();
                    if (edit_temp == '-') {
                        Swal.showValidationMessage(
                            'Isian harus terisi!!'
                        );
                        return false;
                    }

                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: base_url + '/save-edit-host',
                            dataType: 'json',
                            type: 'POST',
                            data: {
                                _token: token,
                                data: {
                                    zoom: zoom,
                                    value: edit_temp,
                                    stat: stat
                                }
                            },
                            success: function (res) {
                                resolve(res);
                            },
                            error: function (err) {
                                reject(err);
                            }
                        })
                    }).then((suc) => {
                        $(`#${stat}_temp${zoom}`).html(edit_temp_text);
                        return suc;
                    }).catch((err) => {
                        Swal.fire({
                            type: 'error',
                            title: 'Error!',
                            html: '<p style="font-size: smaller">Terdapat kesalahan pada jaringan anda. Refresh halaman ini</p>'
                        }).then(function () {
                            location.reload();
                        })
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then(function (val) {
                if (val.value) {
                    Swal.fire({
                        type: 'success',
                        title: 'Sukses!',
                        html: '<p style="font-size: smaller">Berhasil menyimpan perubahan data</p>'
                    })
                }
            })
            
        })

        $('body').on('click', '.edit-zoom', function () {
            $(this).blur();
            // data-rapat="'+data.data[0].id+'" data-val="'+data.data[0].zoom_id+'" data-stat="z-id"     
            let zoom = $(this).data('zoom'),
                val = $(this).data('val'),
                stat = $(this).data('stat');
            Swal.fire({
                type: 'question',
                html: `<div class="loading-temp">${loader}</div>`,
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan Perubahan',
                showLoaderOnConfirm: true,
                onOpen: () => {
                    $.ajax({
                        url: base_url + '/get-zoom-rinc',
                        dataType: 'json',
                        type: 'POST',
                        data: {
                            _token: token,
                            data: zoom
                        },
                        success: function (data) {
                            if (data.status == 'forbidden') {
                                Swal.fire({
                                    type: 'error',
                                    title: 'Wadooooh!',
                                    html: '<p>Hanya admin yang bisa merubah segalanyaaa~</p>'
                                });
                                return false;
                            }

                            let val_temp = stat == 'z-id' ? data.result
                                .zoom_id : (stat == 'z-pw' ? data.result
                                    .zoom_password : data.result.zoom_link);
                            $('.loading-temp').html(`
                            <p style="font-size: ; text-transform: uppercase"><strong>Masukan ${stat == 'z-id' ? 'Zoom ID' : (stat == 'z-pw' ? 'Zoom Password' : 'Link Zoom')} Meeting</strong></p>
                            <input style="margin-bottom: 15px" type="text" class="form-control edit-zoom-field" value="${val_temp ? val_temp : ''}" />
                            `);
                        },
                        error: function (err) {
                            Swal.fire({
                                type: 'error',
                                title: 'Error!',
                                html: '<p style="font-size: smaller">Terdapat kesalahan pada jaringan anda. Refresh halaman ini</p>'
                            }).then(function () {
                                location.reload();
                            })
                        }
                    })
                },
                preConfirm: () => {
                    let edit_temp = $(".edit-zoom-field").val().trim();
                    if (edit_temp.length == 0) {
                        Swal.showValidationMessage(
                            'Isian harus terisi!!'
                        );
                        return false;
                    }

                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: base_url + '/save-edit-zoom',
                            dataType: 'json',
                            type: 'POST',
                            data: {
                                _token: token,
                                data: {
                                    zoom: zoom,
                                    value: edit_temp,
                                    stat: stat
                                }
                            },
                            success: function (res) {
                                resolve(res);
                            },
                            error: function (err) {
                                reject(err);
                            }
                        })
                    }).then((suc) => {
                        $(`#${stat}_temp${zoom}`).html((stat == 'z-link' ?
                            '<a target="_blank" href="' + edit_temp + '">' +
                            edit_temp + '</a>' : edit_temp));
                        return suc;
                    }).catch((err) => {
                        Swal.fire({
                            type: 'error',
                            title: 'Error!',
                            html: '<p style="font-size: smaller">Terdapat kesalahan pada jaringan anda. Refresh halaman ini</p>'
                        }).then(function () {
                            location.reload();
                        })
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then(function (val) {
                if (val.value) {
                    Swal.fire({
                        type: 'success',
                        title: 'Sukses!',
                        html: '<p style="font-size: smaller">Berhasil menyimpan perubahan data</p>'
                    })
                }
            })
        })

        $('body').on('click', '.kirim-notif', function () {
            $(this).blur();
            let zoom = $(this).data('zoom');
            Swal.fire({
                type: 'question',
                html: `<div class="loading-temp">${loader}</div>`,
                showCancelButton: true,
                confirmButtonText: 'Yes, Kirim Notif',
                showLoaderOnConfirm: true,
                onOpen: () => {
                    $.ajax({
                        url: base_url + '/get-pj-zoom',
                        dataType: 'json',
                        type: 'POST',
                        data: {
                            _token: token,
                            data: zoom
                        },
                        success: function (data) {
                            $(".loading-temp").html(
                                `<p>Kirim link zoom meeting ke PJ rapat: </p>
                                <input style="margin-bottom: 10px" type="text" class="form form-control input-send-notif" value="${data.nohp_pj ? data.nohp_pj : ''}" />`
                                );
                        },
                        error: function (err) {
                            Swal.fire({
                                type: 'error',
                                title: 'Error!',
                                html: '<p>Terdapat kesalahan pada jaringan anda. Refresh halaman ini....</p>'
                            }).then(function () {
                                location.reload();
                            })
                        }
                    })
                },
                preConfirm: () => {
                    let input_val = $(".input-send-notif").val().trim();
                    if (input_val.length == 0) {
                        Swal.showValidationMessage(
                            `Isian tidak boleh kosong...`
                        );
                        return false;
                    }

                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: base_url + '/send-notif-zoom',
                            dataType: 'json',
                            type: 'POST',
                            data: {
                                _token: token,
                                data: input_val,
                                zoom: zoom
                            },
                            success: function (data) {
                                resolve(data);
                            },
                            error: function (err) {
                                reject(err);
                            }
                        })
                    }).then((suc) => {
                        return suc;
                    }).catch((err) => {
                        Swal.fire({
                            type: 'error',
                            title: 'Error!',
                            html: '<p>Terdapat kesalahan pada jaringan anda. Refresh halaman ini...</p>'
                        }).then(function () {
                            location.reload();
                        })
                    })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then(function (val) {
                if (val.value) {
                    Swal.fire({
                        type: 'success',
                        title: 'Sukses!',
                        html: '<p>Pesan notifikasi akan segera dikirim ....</p>'
                    })
                }
            })
        })

    })
</script>
@endsection