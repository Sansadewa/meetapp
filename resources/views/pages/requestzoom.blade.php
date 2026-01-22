@extends('layout.index')
@section('title') Daftar Request Zoom Kalsel @endsection

@section('css')
<link rel="stylesheet" type="text/css" href="{{ url('public/assets/select2/select2.min.css') }}">
<style>
</style>
@endsection

@section('content')
<!-- ============================================================== -->
<!-- Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
<div class="row page-titles">
    <div class="col-md-5 col-12 align-self-center">
        <h3 class="text-themecolor mb-0">Zoom Kalsel</h3>
    </div>
    <div class="col-md-7 col-12 align-self-center d-none d-md-flex justify-content-end">
        <ol class="breadcrumb mb-0 p-0 bg-transparent">
            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
            <li class="breadcrumb-item active">Request Zoom</li>
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
                <div class="d-md-flex no-block align-items-center">
                    <h4 class="card-title mb-3"><span class="lstick d-inline-block align-middle"></span> Request Zoom
                        Kalsel</h4>
                </div>
                <div class="alert alert-info alert-dismissible fade show mt-4" role="alert">
                    <span class="badge badge-info"><i class="fas fa-info"></i></span>
                    <strong> Pilih rapat pada pilihan select, kemudian isi zoom meeting id untuk rapat
                        tersebut.</strong>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
                    <span class="badge badge-info"><i class="fas fa-info"></i></span>
                    <strong> Hindari rapat tanggal 3 untuk akun bps6300@gmail.com dan tanggal 17 untuk ipds6300@gmail.com</strong>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="row">
                    <div>

                    </div>
                    <div class="col-12">
                        <select class="select2 form-control custom-select" style="width: 100%; height:36px;">
                            <option value="-">-- Pilih Rapat --</option>
                            @foreach($merge as $data)
                            <option value="{{ $data->id }}">{{ strtoupper($data->nama_unit_kerja) }}: {{ $data->nama }}
                            </option>
                            @endforeach

                        </select>
                    </div>
                </div>
            </div>
            <hr>
            <div class="card-body" id="zoom-data">
                <div class="alert alert-success" role="alert">
                    <h4 class="alert-heading">DETAIL RAPAT</h4>
                    <hr>
                    <p>Nama: <span id="nama_rapat_detail"></span></p>
                    <p>Topik: <span id="topik_rapat_detail"></span></p>
                    <p>Latar Belakang: <span id="latar_belakang_rapat_detail"></span></p>
                    <p class="mb-0">Waktu: <span id="waktu_rapat_detail"></span></p>
                </div>
                <div class="table-responsive">
                    <table id="table_zoom" class="table table-striped table-bordered display">
                        <thead>
                            <tr>
                                <th width="20%">Tanggal Rapat</th>
                                <th width="25%">Zoom ID</th>
                                <th width="25%">Zoom Password</th>
                                <th width="15%">Zoom Link</th>
                                <th width="15%">Host Zoom</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Tanggal Rapat</th>
                                <th>Zoom ID</th>
                                <th>Zoom Password</th>
                                <th>Zoom Link</th>
                                <th>Host Zoom</th>
                                <th>Action</th>
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
                <h4 class="modal-title text-white" id="info-header-modalLabel">Zoom Meeting</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="spinner" id="loader-modal">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                    <div class="bounce3"></div>
                </div>

                <div class="row container-modal" style="display:none">
                    <div class="col-sm-12 col-xs-12">
                        <form id="form-modal">
                            <div class="form-group">
                                <label for="zoom_id_modal">Zoom ID</label>
                                <input type="text" class="form-control form-control-danger" id="zoom_id_modal"
                                    placeholder="Enter Zoom ID">

                            </div>
                            <div class="form-group">
                                <label for="zoom_pw_modal">Zoom Password</label>
                                <input type="text" class="form-control" id="zoom_pw_modal"
                                    placeholder="Enter Zoom Password">
                            </div>
                            <div class="form-group">
                                <label for="zoom_link_modal">Link Zoom</label>
                                <input type="text" class="form-control" id="zoom_link_modal"
                                    placeholder="Enter Link Zoom">
                            </div>
                            <div class="form-group">
                                <label for="host_zoom">Host Zoom Meeting</label>
                                <select class="form-control" id="host_zoom">
                                    <option value="-">-- Pilih Host Zoom Meeting --</option>
                                </select>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit"
                    class="btn btn-success waves-effect waves-light mr-2 save-data-zoom">Submit</button>
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
<script src="{{ url('public/assets/select2/select2.min.js')}}"></script>
<script src="{{ url('public/assets/block-ui/jquery.blockUI.js')}}"></script>
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

        var tabel_zoom = $("#table_zoom").DataTable({
            searching: false,
            columns: [{
                    data: 'waktu'
                },
                {
                    data: 'zoom_id'
                },
                {
                    data: 'zoom_pw'
                },
                {
                    data: 'zoom_link'
                },
                {
                    data: 'host_zoom'
                },
                {
                    data: 'action'
                }
            ],
        });

        $(".select2").select2();


        $(".select2").on('change', function () {
            var block_ele = $(this).closest('.card'),
                rapat = $(this).val();
            $(block_ele).block({
                message: '<i class="fas fa-spin fa-sync text-white"></i>',
                // timeout: 2000, //unblock after 2 seconds
                overlayCSS: {
                    backgroundColor: '#000',
                    opacity: 0.5,
                    cursor: 'wait'
                },
                css: {
                    border: 0,
                    padding: 0,
                    backgroundColor: 'transparent'
                }
            });
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: base_url + '/detail-rapat',
                    dataType: 'json',
                    type: 'POST',
                    data: {
                        _token: token,
                        data: rapat
                    },
                    success: function (data) {
                        resolve(data);
                    },
                    error: function (err) {
                        reject(err.responseText);
                    }
                });
            }).then((succ) => {            
                block_ele.unblock();
                document.getElementById("zoom-data").scrollIntoView();
                $("#nama_rapat_detail").html(succ.rapat.nama);
                $("#topik_rapat_detail").html(succ.rapat.topik);
                $("#latar_belakang_rapat_detail").html(succ.rapat.latar_belakang ? succ.rapat
                    .latar_belakang : '-');
                $("#waktu_rapat_detail").html((moment(succ.rapat.tanggal_rapat_start).isSame(
                        moment(succ.rapat.tanggal_rapat_end)) ? moment(succ.rapat
                        .tanggal_rapat_start).format("D MMM YYYY") : moment(succ.rapat
                        .tanggal_rapat_start).format("D MMM YYYY") + ' - ' + moment(succ
                        .rapat.tanggal_rapat_end).format("D MMM YYYY")) + ' ' + succ.rapat
                    .waktu_mulai_rapat + ' s.d ' + succ.rapat.waktu_selesai_rapat + ' WITA');
                let diff = moment(succ.rapat.tanggal_rapat_end).diff(moment(succ.rapat
                        .tanggal_rapat_start), 'days'),
                    start_zoom = moment(succ.rapat.tanggal_rapat_start);

                let newData = [];
                for (let i = 0; i <= diff; i++) {
                    let temp_start = start_zoom.clone();
                    let date_temp = moment(temp_start.add(i, 'days'));
                    let filter_temp = succ.zoom.filter((data) => {
                        return (data.tanggal_zoom == date_temp.format('YYYY-MM-DD') &&
                            data.rapat == succ.rapat.id);
                    });
                    
                    newData.push({
                        'waktu': date_temp.format('D MMM YYYY'),
                        'zoom_id': filter_temp.length > 0 ? filter_temp[0].zoom_id :
                            '-',
                        'zoom_pw': filter_temp.length > 0 ? filter_temp[0]
                            .zoom_password : '-',
                        'zoom_link': filter_temp.length > 0 ? filter_temp[0].zoom_link :
                            '-',
                        'host_zoom': filter_temp.length > 0 ? filter_temp[0].nama_host :
                            '-',
                        'action': `
                                    <button data-tanggal="${date_temp.format('YYYY-MM-DD')}" data-rapat = "${succ.rapat.id}" class="btn btn-success btn-sm edit-zoom" data-toggle="tooltip" data-placement="right" title="Edit Data Zoom"><i class="ti ti-pencil-alt"></i> 
                                    Edit</button>    
                                `
                    });

                }

                tabel_zoom.clear();
                tabel_zoom.rows.add(newData).draw();
            }).catch((err) => {
                Swal.fire({
                    type: 'error',
                    title: 'Error!',
                    html: '<p style="font-size: smaller">Terdapat kesalahan pada jaringan anda. Refresh halaman ini ...</p>'
                }).then(function () {
                    location.reload();
                })
                // console.log(err);
            });
        });

        let rapat_selected, tanggal_selected, row_edited;
        $("#table_zoom tbody").on('click', '.edit-zoom', function () {
            tanggal_selected = $(this).data('tanggal');
            rapat_selected = $(this).data('rapat');
            row_edited = tabel_zoom.row($(this).parents());
            let data_row = tabel_zoom.row($(this).parents()).data();

            $.ajax({
                url: base_url+'/get-calon-host',
                dataType: 'json',
                type: 'GET',
                success: function(data) {
                    let option = `<option value="-">-- Pilih Host Zoom Meeting --</option>`;
                    for(let i=0; i<data.result.length; i++)
                    {
                        option += `<option value="${data.result[i].id}">${data.result[i].nama}</option>`;
                    }

                    let host = data.host_zoom;

                    $("#zoom_id_modal").val(data_row.zoom_id == '-' ? '' : data_row.zoom_id);
                    $("#zoom_pw_modal").val(data_row.zoom_pw == '-' ? '' : data_row.zoom_pw);
                    $("#zoom_link_modal").val(data_row.zoom_link == '-' ? '' : data_row.zoom_link);
                    $("#host_zoom").html(option);
                    $("#host_zoom option").filter(function(){ return $(this).text() == host;}).prop('selected', true);
                    
                    $('.container-modal').show();
                    $("#loader-modal").hide();
                },
                error: function(err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: `<p>Terdapat kesalahan pada jaringan anda. Refresh halaman ini. Terima kasih</p>`
                    });
                    console.log(err.responseText);
                }
            })



            $("#modal-zoom").modal('toggle');
        });

        $("#modal-zoom").on('hidden.bs.modal', function () {
            $(".container-modal").hide();
            $("#loader-modal").show();
            $("#form-modal").trigger('reset');
        })

        $("#table_zoom tbody").on('click', '.kirim-notif', function () {
            let ztmp = $(this).data('ztmp');
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
                            data: ztmp
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
                                zoom: ztmp
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

        $('.save-data-zoom').click(function () {
            let data = {
                'zoom_id': $("#zoom_id_modal").val().trim(),
                'zoom_pw': $("#zoom_pw_modal").val().trim(),
                'zoom_link': $("#zoom_link_modal").val().trim(),
                'rapat': rapat_selected,
                'tanggal': tanggal_selected,
                'host': $("#host_zoom").val(),
                'host_name': $("#host_zoom option:selected").text()
            };

            if (data.zoom_id.length == 0 || data.zoom_pw.length == 0 || data.zoom_link.length == 0 || data.host == '-') {
                Swal.fire({
                    type: 'warning',
                    title: 'Wadooh',
                    html: '<p style="font-size: smaller">Semua isian harus terisi</p>'
                })
            } else {
                Swal.fire({
                    type: 'question',
                    title: 'Konfirmasi!',
                    html: '<p>Simpan isian ?</p>',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return new Promise((resolve, reject) => {
                            $.ajax({
                                url: base_url + '/save-zoom-rapat',
                                dataType: 'json',
                                type: 'POST',
                                data: {
                                    _token: token,
                                    data: data
                                },
                                success: function (succ) {
                                    resolve(succ);
                                },
                                error: function (err) {
                                    reject(err);
                                }
                            })
                        }).then((suc) => {
                            // update datatable ...
                            row_edited.data().zoom_id = data.zoom_id;
                            row_edited.data().zoom_pw = data.zoom_pw;
                            row_edited.data().zoom_link = data.zoom_link;
                            row_edited.data().host_zoom = data.host_name;
                            row_edited.data().action += `<br/><button style="margin-top: 10px;" data-ztmp="${suc.zoom_fn}" class="btn btn-info btn-sm kirim-notif" data-toggle="tooltip" data-placement="right" title="Kirim Notif WA"><i class="ti ti-bell"></i> 
                                    Notif WA</button>
                            `;
                            tabel_zoom.rows().invalidate().draw();
                            $("#modal-zoom").modal('toggle');
                            return suc;
                        }).catch((err) => {
                            console.log(err);
                        })
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then(function (val) {
                    console.log(val);
                    if (val.value) {
                        Swal.fire({
                            type: 'success',
                            title: 'Sukses!',
                            html: '<p>Berhasil menyimpan data</p>'
                        })
                    } else if(val.hasOwnProperty("dismiss"))
                    {
                        return;
                    } else {
                        Swal.fire({
                            type: 'error',
                            title: 'Error!',
                            html: '<p style="font-size:smaller">Terdapat kesalahan pada jaringan anda. Refresh halaman ini</p>'
                        }).then(function () {
                            location.reload();
                        })
                    }
                })
            }
        })

    });
</script>
@endsection