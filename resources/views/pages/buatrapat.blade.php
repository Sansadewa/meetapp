@extends('layout.index')
@section('title') Buat Rapat @endsection

@section('css')
<link rel="stylesheet" type="text/css" href="{{ url('public/assets/fullcalendar/fullcalendar.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ url('public/assets/datepicker/bootstrap-datepicker.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ url('public/assets/toastr/toastr.min.css') }}">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-theme@0.1.0-beta.10/dist/select2-bootstrap.min.css" rel="stylesheet" />
<style>
    /* Style Select2 close button - remove border and make it red */
    .select2-selection__choice__remove {
        border: none !important;
        color: #dc3545 !important;
        margin-right: 5px;
        cursor: pointer;
        background-color: transparent !important;
    }
    .select2-selection__choice__remove:hover {
        color: #c82333 !important;
    }
    .select2-search__field {
        padding: 2% 12px !important;
    }
    .select2-selection__choice{
        color: #fff !important;
        background-color: #398bf7bd !important;
    }
    /* Toggle Switch Styles */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
        vertical-align: middle;
    }
    .toggle-switch input[type="checkbox"] {
        opacity: 0;
        width: 0;
        height: 0;
        position: absolute;
        z-index: 1;
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
        border-radius: 34px;
        z-index: 0;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .toggle-switch input[type="checkbox"]:checked + .toggle-slider {
        background-color: #28a745;
    }
    .toggle-switch input[type="checkbox"]:checked + .toggle-slider:before {
        transform: translateX(26px);
    }
    .toggle-switch input[type="checkbox"]:focus + .toggle-slider {
        box-shadow: 0 0 1px #28a745;
    }
    .toggle-switch:hover .toggle-slider {
        box-shadow: 0 0 3px rgba(0,0,0,0.3);
    }
    /* Participant Tag Truncation */
    .select2-selection__choice {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        position: relative;
        display: inline-block;
    }
    .select2-selection__choice[title] {
        cursor: help;
    }
    /* Ensure Select2 choices show full text on hover */
    .select2-container--bootstrap .select2-selection__choice {
        max-width: 200px;
    }
    /* Static Unit Kerja Subtitle */
    .unit-kerja-subtitle {
        color: #6c757d;
        font-size: 0.9rem;
        font-weight: normal;
    }
    /* Form spacing improvements */
    .form-group {
        margin-bottom: 1rem;
    }
</style>
@endsection

@section('content')
<!-- ============================================================== -->
<!-- Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
<div class="row page-titles">
    <div class="col-md-5 col-12 align-self-center">
        <h3 class="text-themecolor mb-0">Buat Rapat</h3>
    </div>
    <div class="col-md-7 col-12 align-self-center d-none d-md-flex justify-content-end">
        <ol class="breadcrumb mb-0 p-0 bg-transparent">
            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
            <li class="breadcrumb-item active">Buat Rapat</li>
        </ol>
    </div>
</div>
<!-- ============================================================== -->
<!-- End Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="">
                <div class="row">
                    <div class="col-lg-2 border-right pr-0">
                        <div class="card-body border-bottom">
                            <h4 class="card-title mt-2">Keterangan</h4>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div id="calendar-events" class="">
                                        <div class="text-muted small" id="legend-loading">
                                            <i class="fa fa-spinner fa-spin mr-1"></i> Memuat keterangan...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-10">
                        <div class="card-body b-l calender-sidebar">
                            <div id="calendar">
                                <div class="spinner" id="spin-calendar">
                                    <div class="bounce1"></div>
                                    <div class="bounce2"></div>
                                    <div class="bounce3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal none-border" id="my-event">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><strong><span id="judul_modal_rapat">Buat Rapat</span></strong></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body" id="body-rapat">
                <div class="spinner">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                    <div class="bounce3"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary waves-effect share-event" data-toggle="tooltip" title="Bagikan Link Rapat"><i class="fa fa-share-alt"></i> Share</button>
                <button type="button" class="btn btn-success save-event waves-effect waves-light"><i
                        class="fa fa-check"></i> Simpan</button>
                <button type="button" class="btn btn-danger delete-event waves-effect waves-light"><i class="fa fa-trash"></i> Delete</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="{{ url('public/assets/moment/moment.min.js')}}"></script>
<script src="{{ url('public/assets/fullcalendar/fullcalendar.min.js')}}"></script>
<script src="{{ url('public/assets/datepicker/bootstrap-datepicker.min.js')}}"></script>
<script src="{{ url('public/assets/toastr/toastr.min.js')}}"></script>
<script src="{{ url('public/js/calendar-rapat.js')}}?v{{filemtime(base_path().'/public/js/calendar-rapat.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('body').on('focus', '#datepicker-tgl-rapat', function () {
            $(this).datepicker({
                autoclose: true,
                todayHighlight: true
            });
        });

        $("body").on('change', "#is_use_zoom", function () {
            var isChecked = $(this).is(':checkbox') ? $(this).is(':checked') : (this.value === "1" || this.value === 1);
            if (isChecked) {
                $("#wa_container").slideDown();
            } else {
                $("#wa_container").slideUp();
            }
        })
        
        // Update participant count dynamically
        $("body").on('change', "select[name='attendees[]']", function () {
            var count = $(this).val() ? $(this).val().length : 0;
            var label = $(this).closest('.form-group').find('label');
            var baseLabel = label.data('base-label') || 'Peserta Rapat';
            label.data('base-label', baseLabel);
            label.html(baseLabel + ' <span class="badge badge-info">(Total: ' + count + ')</span>');
        })

        $("#my-event").on('hidden.bs.modal', function(){
            $("#body-rapat").html(`<div class="spinner">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                    <div class="bounce3"></div>
                </div>`);
        })
        // $('#datepicker-tgl-rapat').datepicker({
        //     autoclose: true,
        //     todayHighlight: true
        // });
    })
</script>
@endsection