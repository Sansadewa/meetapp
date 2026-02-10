@extends('layout.index')
@section('title') Home @endsection

@section('css')
<link rel="stylesheet" type="text/css" href="{{ url('public/assets/chartist/chartist.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ url('public/assets/chartist/chartist-init.css') }}">
<link rel="stylesheet" type="text/css" href="{{ url('public/assets/chartist/chartist-plugin-tooltip.css') }}">
<style>
</style>
@endsection

@section('content')
<!-- ============================================================== -->
<!-- Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
<div class="row page-titles">
    <div class="col-md-5 col-12 align-self-center">
        <h3 class="text-themecolor mb-0">MeetApp Anda Hari Ini</h3>
        {{-- <div class="d-flex justify-content-center my-3">
            <div class="form-check form-switch">
                <label class="form-check-label" for="flexSwitchCheckDefault">Sembunyikan Ruangan Kosong</label>
                <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
            </div>
        </div> --}}
    </div>
    <div class="col-md-7 col-12 align-self-center d-none d-md-flex justify-content-end">
        <ol class="breadcrumb mb-0 p-0 bg-transparent">
            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </div>
</div>
<!-- ============================================================== -->
<!-- End Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->

{{-- <div class="row">
    <div class="col-lg-4 col-md-6">
        <div class="card border-bottom border-info">
            <div class="card-body">
                <div class="d-flex no-block align-items-center">
                    <div>
                        <h2>{{$bulan_ini->count()}}</h2>
                        <h6 class="text-info">Rapat Bulan Ini</h6>
                    </div>
                    <div class="ml-auto">
                        <span class="text-info display-6"><i class="ti-notepad"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="card border-bottom border-cyan">
            <div class="card-body">
                <div class="d-flex no-block align-items-center">
                    <div>
                        <h2>{{ $tahun_ini->count() }}</h2>
                        <h6 class="text-cyan">Rapat Tahun Ini</h6>
                    </div>
                    <div class="ml-auto">
                        <span class="text-cyan display-6"><i class="ti-clipboard"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="card border-bottom border-success">
            <div class="card-body">
                <div class="d-flex no-block align-items-center">
                    <div>
                        <h2>{{ $total }}</h2>
                        <h6 class="text-success">Total Rapat</h6>
                    </div>
                    <div class="ml-auto">
                        <span class="text-success display-6"><i class="ti-wallet"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex">
                    <div>
                        <h3 class="card-title mb-1"><span class="lstick d-inline-block align-middle"></span>Grafik Rapat - {{ session('level') != 2 ? session('nama_unit_kerja') : 'BPS Prov Kalsel'}}
                        </h3>
                    </div>
                    <div class="ml-auto">
                        <select class="custom-select border-0 change-year" style="cursor:pointer">
                            <option {{ date('Y') == '2020' ? 'selected' : '' }} value="2020">2020</option>
                            <option {{ date('Y') == '2021' ? 'selected' : '' }} value="2021">2021</option>
                            <option {{ date('Y') == '2022' ? 'selected' : '' }} value="2022">2022</option>
                            <option {{ date('Y') == '2023' ? 'selected' : '' }} value="2023">2023</option>
                            <option {{ date('Y') == '2024' ? 'selected' : '' }} value="2024">2024</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="spinner spin-grafik">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                    <div class="bounce3"></div>
                </div>
                <div id="grafik-rapat" class="position-relative" style="height:340px;"></div>
            </div>
        </div>
    </div>
</div> --}}
<div class="row">
    @if(isset($userMeetings) && $userMeetings->count() > 0)
        @foreach($userMeetings as $meeting)
        {{-- <div class="col-lg-3 col-xs-12 mb-4">
            <div class="card shadow" style="border-radius: 20px">
                <div class="card-header py-4 d-flex justify-content-center align-items-center bg-primary" style="border-radius: 20px 20px 0 0">
                    <h4 class="m-0 font-weight-bold text-light">{{ $meeting->ruang_rapat }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>{{ $meeting->nama }}</strong>
                    </div>
                    <div>
                        <p class="mb-0">{{ $meeting->waktu_mulai_rapat }} - {{ $meeting->waktu_selesai_rapat }}</p>
                    </div>
                </div>
            </div>
        </div> --}}
        <div class="col-lg-3 col-md-6 col-xs-12 mb-4">
            <div class="card shadow-sm h-100 border-0" style="border-radius: 16px; border-left: 6px solid #4e73df !important; "> 
                
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="font-weight-bold text-dark mb-3">
                            {{ $meeting->nama }}
                        </h5>
                        <div class="text-muted small mb-2 font-weight-bold" style="letter-spacing: 0.5px;">
                            {{ substr($meeting->waktu_mulai_rapat, 0, 5) }} - {{ substr($meeting->waktu_selesai_rapat, 0, 5) }}
                        </div>

                        
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        
                        <span class="badge badge-light text-primary px-3 py-2" style="border-radius: 8px; background-color: #f0f4ff;">
                            <i class="fa fa-map-marker-alt mr-1"></i> {{ $meeting->ruang_rapat }}
                        </span>

                        <button id="share-link" data-toggle="tooltip" data-placement="top" title="Share" href="{{ url("/s/" . $meeting->uid) }}" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" >
                            <i class="fa fa-share-alt"></i>
                        </button>
                        <a data-toggle="tooltip" data-placement="top" title="Lihat Detil" href="{{ url("/s/" . $meeting->uid) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    @else
        <div class="col-12">
            <div class="alert alert-info text-center">
                <h5>Anda tidak ada MeetApp untuk hari ini.</h5>
            </div>
        </div>
    @endif
</div>


@endsection

@section('js')
<script src="{{ url('public/assets/chartist/chartist.min.js')}}"></script>
<script src="{{ url('public/assets/chartist/chartist-plugin-tooltip.min.js')}}"></script>
<script>
    $(document).ready(function () {
        const copyButton = document.getElementById('share-link');
        const meetingUrl = copyButton.getAttribute('href');
        copyButton.addEventListener('click', function() {
            // Copy URL to clipboard
            navigator.clipboard.writeText(meetingUrl).then(function() {
                copyButton.innerHTML = '<i class="fa fa-check"></i>';
                copyButton.classList.remove('btn-outline-primary');
                copyButton.classList.add('btn-outline-success');
                setTimeout(function() {
                    copyButton.innerHTML = '<i class="fa fa-share-alt"></i>';
                    copyButton.classList.remove('btn-outline-success');
                    copyButton.classList.add('btn-outline-primary');
                }, 2000);
            });
        });

        let token = document.head.querySelector('meta[name="_token"]').content,
            base_url = document.head.querySelector('meta[name="base_url"]').content,
            loader = `
                    <div class="spinner">
                        <div class="bounce1"></div>
                        <div class="bounce2"></div>
                        <div class="bounce3"></div>
                    </div>
                `,
            chart_rapat;
        
        const getGrafikRapat = (tahun) => {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: base_url + '/get-data-grafik',
                    dataType: 'json',
                    type: 'POST',
                    data: {
                        _token: token,
                        data: tahun
                    },
                    success: function (data) {
                        resolve(data);
                    },
                    error: function (err) {
                        reject(err);
                    }
                })
            })
        }

        const dataRapat = async () => {
            return await getGrafikRapat(new Date().getFullYear());
        }

        dataRapat().then((suc) => {
            new Chartist.Line('#grafik-rapat', {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Okt',
                    'Nov', 'Des'
                ],
                series: [{
                    meta: "Total Rapat",
                    data: Object.values(suc)
                }]
            }, {
                low: 0,
                high: 50,
                showArea: true,
                divisor: 10,
                lineSmooth: false,
                fullWidth: true,
                showLine: true,
                chartPadding: 30,
                axisX: {
                    showLabel: true,
                    showGrid: false,
                    offset: 50
                },
                plugins: [
                    Chartist.plugins.tooltip()
                ],
                // As this is axis specific we need to tell Chartist to use whole numbers only on the concerned axis
                axisY: {
                    onlyInteger: true,
                    showLabel: true,
                    scaleMinSpace: 50,
                    showGrid: true,
                    offset: 10,
                    labelInterpolationFnc: function (value) {
                        // return (value / 100) + 'k'
                        return value;
                    },

                }

            });

            $('.spin-grafik').hide();
        }).catch((err) => {
            Swal.fire({
                type: 'error',
                title: 'Error!',
                html: '<p style="font-size:smaller">Terdapat kesalahan pada jaringan anda. Refresh halaman ini ....</p>'
            }).then(function () {
                location.reload();
            })
        });

        $('.change-year').on('change', function () {
            $('.spin-grafik').show();
            $("#grafik-rapat").hide();
            let newYear = $(this).val();
            const newData = async () => {
                return await getGrafikRapat(newYear);
            }

            newData().then((suc) => {
                new Chartist.Line('#grafik-rapat', {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug',
                        'Sept', 'Okt',
                        'Nov', 'Des'
                    ],
                    series: [{
                        meta: "Total Rapat",
                        data: Object.values(suc)
                    }]
                }, {
                    low: 0,
                    high: 50,
                    showArea: true,
                    divisor: 10,
                    lineSmooth: false,
                    fullWidth: true,
                    showLine: true,
                    chartPadding: 30,
                    axisX: {
                        showLabel: true,
                        showGrid: false,
                        offset: 50
                    },
                    plugins: [
                        Chartist.plugins.tooltip()
                    ],
                    // As this is axis specific we need to tell Chartist to use whole numbers only on the concerned axis
                    axisY: {
                        onlyInteger: true,
                        showLabel: true,
                        scaleMinSpace: 50,
                        showGrid: true,
                        offset: 10,
                        labelInterpolationFnc: function (value) {
                            // return (value / 100) + 'k'
                            return value;
                        },

                    }

                });

                $('.spin-grafik').hide();
                $("#grafik-rapat").show();
            }).catch((err) => {
                Swal.fire({
                    type: 'error',
                    title: 'Error!',
                    html: '<p style="font-size:smaller">Terdapat kesalahan pada jaringan anda. Refresh halaman ini ....</p>'
                }).then(function () {
                    location.reload();
                })
            })
        })
    })
</script>
@endsection