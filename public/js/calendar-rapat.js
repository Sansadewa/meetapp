! function($) {
    "use strict";    

    let token = document.head.querySelector('meta[name="_token"]').content,
        base_url = document.head.querySelector('meta[name="base_url"]').content,
        loader = `<div class="spinner">
                <div class="bounce1"></div>
                <div class="bounce2"></div>
                <div class="bounce3"></div>
                </div>`;
    
    const getUnitKerja = (user_id = null) => {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: base_url+'/get-unit-kerja',
                dataType: 'json',
                type: 'GET',
                data: user_id ? {user_id: user_id} : {},
                success: function(data) {
                    resolve(data);
                },
                error: function(err) {
                    reject(err);
                }
            })
        })
    }

    const getRuang = () => {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: base_url+'/get-ruang',
                dataType: 'json',
                type: 'GET',
                success: function(data) {
                    resolve(data);
                },
                error: function(err) {
                    reject(err);
                }
            })
        })
    }

    const getDataEditRapat = (rapat) => {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: base_url+'/get-data-rapat-edit',
                dataType: 'json',
                type: 'POST',
                data: {_token: token, data: rapat},
                success: function(data) {                    
                    resolve(data);
                },
                error: function(err) {
                    reject(err);
                }
            })
        })
    }

    const getDataRapat = () => {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: base_url + '/get-data-rapat',
                dataType: 'json',
                type: 'GET',
                success: function (data) {
                    resolve(data);
                },
                error: function (err) {
                    reject(err);
                }
            })
        })
    }

    const opsi_unit_kerja = async (user_id = null) => {
        return await getUnitKerja(user_id);
    }

    const opsi_ruang = async () => {
        return await getRuang();
    }

    const data_edit_rapat = async (rapat) => {
        return await getDataEditRapat(rapat);
    }

    // Custom Fields Helper Functions
    const customFieldsHTML = (customFields = [], allowEdit = true) => {
        let html = `
            <div class='col-md-12 mb-4'>
                <div class='form-group'>
                    <label class='control-label'>
                        Field Tambahan (Opsional)
                        ${allowEdit ? '<button type="button" class="btn btn-sm btn-success ml-2" id="add_custom_field"><i class="fa fa-plus"></i> Tambah Field</button>' : ''}
                    </label>
                    <div id='custom_fields_container' class='mt-2'>
        `;
        
        if (customFields && customFields.length > 0) {
            customFields.forEach((field, index) => {
                html += createCustomFieldRow(field.field_key, field.field_value, index, allowEdit);
            });
        }
        
        html += `
                    </div>
                    <small class="form-text text-muted">Tambahkan informasi tambahan seperti link Presensi, Link Rekaman, Link Materi, dll.</small>
                </div>
            </div>
        `;
        return html;
    };

    const createCustomFieldRow = (key = '', value = '', index = 0, allowEdit = true) => {
        if (!allowEdit) {
            return `
                <div class="custom-field-row mb-2 p-2" style="background: #f8f9fa; border-radius: 4px; border-left: 3px solid #4e73df;">
                    <div class="row">
                        <div class="col-md-5">
                            <strong>${key}</strong>
                        </div>
                        <div class="col-md-7">
                            ${value.startsWith('http') ? `<a href="${value}" target="_blank">${value}</a>` : value}
                        </div>
                    </div>
                </div>
            `;
        }
        
        return `
            <div class="custom-field-row mb-2" data-index="${index}">
                <div class="row">
                    <div class="col-md-5">
                        <input type="text" class="form-control form-control-sm" name="custom_field_key[]" placeholder="Nama Informasi Tambahan (e.g., Link Rekaman)" value="${key}" />
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control form-control-sm" name="custom_field_value[]" placeholder="Isian (text atau link)" value="${value}" />
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-danger remove_custom_field" style="width: 100%;"><i class="fa fa-times"></i></button>
                    </div>
                </div>
            </div>
        `;
    };

    // Function to update calendar legend based on loaded events
    const updateCalendarLegend = (events) => {
        var $container = $('#calendar-events');
        
        // Extract unique unit_kerja from events
        var uniqueUnits = {};
        events.forEach(function(item) {
            var key = item.singkatan_unit_kerja;
            if (!uniqueUnits[key]) {
                uniqueUnits[key] = {
                    nama: item.nama_unit_kerja,
                    singkatan: item.singkatan_unit_kerja,
                    class_bg: item.class_bg,
                    count: 0
                };
            }
            uniqueUnits[key].count++;
        });
        
        // Handle empty state
        if (Object.keys(uniqueUnits).length === 0) {
            $container.html('<div class="text-muted small">Tidak ada rapat di periode ini</div>');
            return;
        }
        
        // Sort alphabetically by full name
        var sortedUnits = Object.values(uniqueUnits).sort(function(a, b) {
            return a.nama.localeCompare(b.nama);
        });
        
        // Render legend items
        $container.empty();
        sortedUnits.forEach(function(unit) {
            $container.append(
                '<div class="calendar-events mb-3">' +
                    '<i class="fa fa-circle text-' + unit.class_bg + ' mr-2"></i>' +
                    unit.nama + ' (' + unit.singkatan + ') ' +
                    '<span class="badge badge-secondary">' + unit.count + '</span>' +
                '</div>'
            );
        });
    };




    var CalendarApp = function() {
        this.$body = $("body")
        this.$calendar = $('#calendar'),
            this.$event = ('#calendar-events div.calendar-events'),
            this.$categoryForm = $('#add-new-event form'),
            this.$extEvents = $('#calendar-events'),
            this.$modal = $('#my-event'),
            this.$saveCategoryBtn = $('.save-category'),
            this.$calendarObj = null
    };


    /* on drop */
    CalendarApp.prototype.onDrop = function(eventObj, date) {                
        
            // var $this = this;
            // // retrieve the dropped element's stored Event Object
            // var originalEventObject = eventObj.data('eventObject');
            // var $categoryClass = eventObj.attr('data-class');
            // // we need to copy it, so that multiple events don't have a reference to the same object
            // var copiedEventObject = $.extend({}, originalEventObject);
            // // assign it the date that was reported
            // copiedEventObject.start = date;
            // if ($categoryClass)
            //     copiedEventObject['className'] = [$categoryClass];
            // // render the event on the calendar
            // $this.$calendar.fullCalendar('renderEvent', copiedEventObject, true);
            // // is the "remove after drop" checkbox checked?
            // if ($('#drop-remove').is(':checked')) {
            //     // if so, remove the element from the "Draggable Events" list
            //     eventObj.remove();
            // }
        },
    CalendarApp.prototype.onDragDrop = function(info) {
        // toastr.success('I do not think that word means what you think it means.', 'Slide Down / Slide Up!', { "showMethod": "fadeIn", "hideMethod": "fadeOut", timeOut: 2000 });
        // console.log(moment(info.start).format(), moment(info.end).subtract(1, 'days').format())
        
         $.ajax({
             url: base_url+'/edit-tangal-rapat-drag',
             dataType: 'json',
             type: 'POST',
             data: {_token: token, data:{ 
                 rapat: info.id_rapat, 
                 ruang_rapat: info.ruang_rapat,
                 tanggal_rapat_start: moment(info.start).format("YYYY-MM-DD"), 
                 tanggal_rapat_end: moment(info.end).subtract(1, 'days').format("YYYY-MM-DD"),
                 mulai_rapat: info.waktu_mulai,
                 akhir_rapat: info.waktu_selesai
             }},
            success: function(data) {
                toastr.success('Berhasil merubah tanggal rapat.', 'Yeay!', { "showMethod": "fadeIn", "hideMethod": "fadeOut", timeOut: 2000 });
                // console.log(data);
            },
            error: function(err) {
                toastr.error('Gagal merubah tanggal rapat.', 'Wadooh!', { "showMethod": "fadeIn", "hideMethod": "fadeOut", timeOut: 2000 });
                console.log(err.responseText);
            }
        })
        
    },

    // NON REFATCORED FUNCTION OF EDIT RAPAT
    /* on click on event : nge klik rapat di kotak tanggal UNTUK EDIT RAPAT*/
    CalendarApp.prototype.onEventClick = function(calEvent, jsEvent, view) {
        // console.log(moment(calEvent.end).format(), '\n', moment(calEvent.start).format());        
        
        var $this = this;
        $("#judul_modal_rapat").html("Edit Rapat");
        // Clear any existing subtitle
        $("#judul_modal_rapat").next(".unit-kerja-subtitle").remove();
        $this.$modal.modal({
            backdrop: 'static'
        });
        
         data_edit_rapat(calEvent.id_rapat).then((val) => {
             // Get all unit kerja (not filtered by user) for edit mode
             getUnitKerja(null).then((uk) => {
                 let opsi_uk = '';
                 let isAdmin = val.lvl_ses == '2';
                 let allow_edit = isAdmin ? true : (Array.isArray(val.uk_ses) && val.uk_ses.includes(parseInt(val.result.unit_kerja)));
                
                for (let i = 0; i < uk.result.length; i++) {
                    opsi_uk += `<option value='${uk.result[i].id}' data-bgc="${uk.result[i].class_bg}" data-uk="${uk.result[i].singkatan}" ${uk.result[i].id == val.result.unit_kerja ? 'selected' : ''}>${uk.result[i].nama}</option>`;
                }
                if(uk.result.length == 0) {
                    opsi_uk = `<option value='${val.result.unit_kerja}' selected>${val.result.nama_unit_kerja}</option>`;
                }
                // Get unit kerja name for subtitle and add to modal header if not admin
                let unit_kerja_name = val.result.nama_unit_kerja || '';
                if (!isAdmin && unit_kerja_name) {
                    $("#judul_modal_rapat").after(`<p class="unit-kerja-subtitle mb-0" style="margin-top: -5px;">${unit_kerja_name}</p>`);
                }
                //kalau tidak boleh edit, ganti "Edit Rapat" menjadi "Lihat Rapat"
                if (!allow_edit) {
                    $("#judul_modal_rapat").html("Lihat Rapat");
                }
                return {
                    opsi_uk: opsi_uk,
                    data_edit: val.result,
                    allow_edit: allow_edit,
                    isAdmin: isAdmin
                };
            }).then( (dtr) => {  
                opsi_ruang().then((ruang) => {
                    let opsi_ruang_html = '';
                    for (let i = 0; i < ruang.result.length; i++) {
                        opsi_ruang_html += `<option value='${ruang.result[i].nama_ruang}'>${ruang.result[i].nama_ruang}</option>`;
                    }
                    
                    
                    var form = $("<form></form>");
                    form.append("<div class='row'></div>");
                    var $row = form.find(".row");
                    
                    // Only show unit kerja dropdown for admin users
                    if (dtr.isAdmin) {
                        $row.append(`<div class='col-md-12 mb-4'>
                            <div class='form-group'>
                                <label class='control-label'>Unit Kerja <span style="color:red">*</span></label>
                                <select class='form-control' name='unit_kerja' id='unit_kerja_select_edit'>
                                    ${dtr.opsi_uk}
                                </select>
                            </div>
                        </div>`)
                    }
                    // Row 1: Agenda Rapat (Full width)
                    $row.append(`<div class='col-md-12 mb-4'>
                        <div class='form-group'>
                            <label class='control-label'>Agenda Rapat <span style="color:red">*</span></label>
                            <input class='form-control' placeholder='Masukan agenda rapat' type='text' name='nama_rapat' value="${dtr.data_edit.nama}" />
                        </div>
                    </div>`);
                    
                    // Row 2: Ruang Rapat (Left) and Sewa Zoom (Right) - 2 columns
                    $row.append(`<div class='col-md-6 mb-4'>
                        <div class='form-group'>
                            <label class='control-label'>Ruang Rapat <span style="color:red">*</span></label>
                            <select class='form-control' name='ruang_rapat' id='ruang_rapat2' >
                                <option value='${dtr.data_edit.ruang_rapat}' selected>${dtr.data_edit.ruang_rapat}</option>
                                ${opsi_ruang_html}
                            </select>
                        </div>
                    </div>`);
                    $row.append(`<div class='col-md-6 mb-4'>
                        <div class='form-group'>
                            <label class='control-label'>Sewa Zoom Meeting <span style="color:red">*</span></label>
                            <div class="d-flex align-items-center" style="height: 34px;">
                                <label class="toggle-switch mb-0 mr-3">
                                    <input type="checkbox" name="is_use_zoom" id="is_use_zoom" ${dtr.data_edit.use_zoom == '1' ? 'checked' : ''} value="1">
                                    <span class="toggle-slider"></span>
                                </label>
                                <span id="zoom_label" style="line-height: 34px;">${dtr.data_edit.use_zoom == '1' ? 'Ya' : 'Tidak'}</span>
                            </div>
                        </div>
                    </div>`);
                    
                    // Row 3: Tanggal, Mulai, Selesai - 3 columns
                    $row.append(`<div class='col-md-4 mb-4'>
                        <div class='form-group'>
                            <label class='control-label'>Tanggal Rapat</label>
                            <input type="text" class="form-control" name="tanggal_mulai" disabled value="${moment(dtr.data_edit.tanggal_rapat_start).isSame(moment(dtr.data_edit.tanggal_rapat_end)) ? moment(dtr.data_edit.tanggal_rapat_start).format("D MMM YYYY") : moment(dtr.data_edit.tanggal_rapat_start).format("D MMM YYYY") + ' - ' + moment(dtr.data_edit.tanggal_rapat_end).format("D MMM YYYY")}" />
                            <input type="text" class="form-control" name="tanggal_selesai" hidden value="${moment(dtr.data_edit.tanggal_rapat_end)}" />
                        </div>
                    </div>`);
                    $row.append(`<div class='col-md-4 mb-4'>
                        <div class='form-group'>
                            <label class='control-label'>Mulai <span style="color:red">*</span></label>
                            <input type="time" name="mulai_rapat" class="form-control" value="${dtr.data_edit.waktu_mulai_rapat}">
                        </div>
                    </div>`);
                    $row.append(`<div class='col-md-4 mb-4'>
                        <div class='form-group'>
                            <label class='control-label'>Selesai <span style="color:red">*</span></label>
                            <input type="time" name="akhir_rapat" class="form-control" value="${dtr.data_edit.waktu_selesai_rapat}">
                        </div>
                    </div>`);
                    
                    $row.append(`<div class='col-md-12 mb-4' id="wa_container" style="display: ${dtr.data_edit.use_zoom == '0' ? 'none' : ''}">
                        <div class='form-group'>
                            <label class='control-label'>Nomor WA PJ Rapat <span style="color:red">*</span></label>
                            <input class='form-control' placeholder='Masukan nomor wa (aktif)' type='number' name='nomor_wa' value="${dtr.data_edit.nohp_pj ? dtr.data_edit.nohp_pj : ''}" />
                            <div class="d-flex justify-content-start">
                                <small id="name1" class="badge badge-default badge-info form-text text-white"><i class="mdi mdi-information-outline"></i> Isikan nomor wa untuk menerima pesan id dan link rapat zooom</small>
                            </div>
                        </div>
                    </div>`);
                    
                    $row.append(`<div class='col-md-12 mb-4'><hr style="border-top: 1px solid rgba(0, 0, 0, 0.2);">
                        <div class='form-group' style='position: relative;'>
                            <label class='control-label' data-base-label="Peserta Rapat">Peserta Rapat <span class="badge badge-info" id="attendees_count_edit">(Total: 0)</span></label>
                            <input type='text' class='form-control' id='attendees_search_edit' placeholder='Cari peserta (User atau Unit Kerja)...' ${!dtr.allow_edit ? 'disabled' : ''} autocomplete='off' />
                            <small class="form-text text-muted">Ketik minimal 2 karakter untuk mencari</small>
                            <div id='attendees_autocomplete_edit' class='list-group' style='position: absolute; z-index: 1000; max-height: 200px; overflow-y: auto; display: none; width: calc(100% - 30px); margin-top: 2px; border: 1px solid #ddd; border-radius: 4px;'></div>
                            <div id='attendees_display_edit' class='mt-3' style='min-height: 40px;'></div>
                            <div id='attendees_hidden_edit'></div>
                        </div>
                        <hr style="border-top: 1px solid rgba(0, 0, 0, 0.2);">
                    </div>`);
                    
                    // Add custom fields section
                    const customFieldsData = val.custom_fields || [];
                    $row.append(customFieldsHTML(customFieldsData, dtr.allow_edit));
                    
                    // Hidden unit_kerja for form submission
                    if (dtr.isAdmin) {
                        // Admin: use the visible select value
                        $row.append(`<div class='col-md-12' style="display:none;">
                            <select class='form-control' name='unit_kerja' id='hidden_unit_kerja'>
                                ${dtr.opsi_uk}
                            </select>
                        </div>`);
                        $row.append(`<input type="hidden" name="uid" id="hidden_uid" value="${dtr.data_edit.uid}" />`);

                    } else {
                        // Standard user: use hidden input with current unit_kerja value
                        $row.append(`<input type="hidden" name="unit_kerja" id="hidden_unit_kerja" value="${dtr.data_edit.unit_kerja}" />`);
                        $row.append(`<input type="hidden" name="uid" id="hidden_uid" value="${dtr.data_edit.uid}" />`);
                    }

                    if(dtr.allow_edit)
                    {
                         $this.$modal.find('.share-event').show().end().find('.delete-event').show().end().find('.save-event').show().end().find('.modal-body').empty().prepend(form).end().find('.delete-event').unbind('click').click(function () {
                            Swal.fire({
                                type: 'question',
                                title: 'Konfirmasi Hapus Rapat',
                                html: '<p style="font-weight: bold; font-size: smaller">Apakah anda yakin akan menghapus data rapat ?</p>',
                                showCancelButton: true,
                                confirmButtonText: '<i class="fa fa-trash"></i> Ya, Hapus',
                                showLoaderOnConfirm: true,
                                preConfirm: () => {
                                    return new Promise((resolve, reject) => {
                                        $.ajax({
                                            url: base_url + '/hapus-rapat',
                                            dataType: 'json',
                                            type: 'POST',
                                            data: {
                                                _token: token,
                                                rapat: calEvent.id_rapat
                                            },
                                            success: function (data) {
                                                resolve(data);
                                            },
                                            error: function (err) {
                                                reject(err.responseText);
                                            }
                                        })
                                    }).then((suc) => {
                                        $this.$calendarObj.fullCalendar('removeEvents', function (ev) {
                                            return (ev._id == calEvent._id);
                                        });
                                        $("#my-event").modal('toggle');
                                        return suc;
                                    }).catch((err) => {
                                        Swal.showValidationMessage(
                                            `Request failed: ${err}`
                                        );
                                    })
                                },
                                allowOutsideClick: () => !Swal.isLoading()
                            }).then(function (val) {
                                if(val.value)
                                {
                                    Swal.fire({
                                        type: 'success',
                                        title: 'Sukses!',
                                        html: `<p style="font-size: smaller">Berhasil menghapus data rapat</p>`
                                    })
                                }                            
                            })

                        }).end().find('.save-event').unbind('click').click(function () {
                            form.submit();
                        }).end().find('.share-event').unbind('click').click(function () {
                            // Get the UID from the form or event data
                            var uid = form.find('input[name="uid"]').val();
                            if (!uid) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Simpan Terlebih Dahulu',
                                    text: 'Anda perlu menyimpan rapat terlebih dahulu sebelum membagikan link',
                                    confirmButtonText: 'Mengerti'
                                });
                                return;
                            }
                            // Construct the shareable URL
                            var shareUrl = base_url + '/s/' + uid;
                            
                            // Create a temporary input element
                            var tempInput = document.createElement('input');
                            tempInput.value = shareUrl;
                            document.body.appendChild(tempInput);
                            tempInput.select();
                            navigator.clipboard.writeText(shareUrl);
                            document.execCommand('copy');
                            document.body.removeChild(tempInput);
                            
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Link Tersalin!',
                                html: `
                                    <p>Link berhasil disalin ke clipboard:</p>
                                    <div class="input-group mt-2 mb-3">
                                        <input type="text" class="form-control" value="${shareUrl}" readonly>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" id="copy-again">
                                                <i class="fa fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="small text-muted">Anda bisa membagikan link ini kepada peserta rapat</p>
                                `,
                                showConfirmButton: true,
                                confirmButtonText: 'Tutup'
                            });
                            
                            // Add click handler for the copy button in the alert
                            setTimeout(() => {
                                document.getElementById('copy-again').addEventListener('click', function() {
                                    var input = this.parentElement.previousElementSibling;
                                    input.select();
                                    document.execCommand('copy');
                                    navigator.clipboard.writeText(shareUrl);
                                    
                                    // Show tooltip or feedback
                                    const tooltip = new bootstrap.Tooltip(this, {
                                        title: 'Tersalin!',
                                        trigger: 'manual'
                                    });
                                    tooltip.show();
                                    setTimeout(() => tooltip.hide(), 1000);
                                });
                            }, 100);
                        });
                    } else
                    {
                         $this.$modal.find('.share-event').show().end().find('.delete-event').hide().end().find('.save-event').hide().end().find('.modal-body').empty().prepend(form).end().end().find('.share-event').unbind('click').click(function () {
                                    // Get the UID from the form or event data
                                    var uid = form.find('input[name="uid"]').val();
                                    if (!uid) {
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Simpan Terlebih Dahulu',
                                            text: 'Anda perlu menyimpan rapat terlebih dahulu sebelum membagikan link',
                                            confirmButtonText: 'Mengerti'
                                        });
                                        return;
                                    }
                                    // Construct the shareable URL
                                    var shareUrl = base_url + '/s/' + uid;
                                    
                                    // Create a temporary input element
                                    var tempInput = document.createElement('input');
                                    tempInput.value = shareUrl;
                                    document.body.appendChild(tempInput);
                                    tempInput.select();
                                    document.execCommand('copy');
                                    navigator.clipboard.writeText(shareUrl);
                                    document.body.removeChild(tempInput);
                                    
                                    // Show success message
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Link Tersalin!',
                                        html: `
                                            <p>Link berhasil disalin ke clipboard:</p>
                                            <div class="input-group mt-2 mb-3">
                                                <input type="text" class="form-control" value="${shareUrl}" readonly>
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="button" id="copy-again">
                                                        <i class="fa fa-copy"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <p class="small text-muted">Anda bisa membagikan link ini kepada peserta rapat</p>
                                        `,
                                        showConfirmButton: true,
                                        confirmButtonText: 'Tutup'
                                    });
                                    
                                    // Add click handler for the copy button in the alert
                                    setTimeout(() => {
                                        document.getElementById('copy-again').addEventListener('click', function() {
                                            var input = this.parentElement.previousElementSibling;
                                            input.select();
                                            document.execCommand('copy');
                                            navigator.clipboard.writeText(shareUrl);
                                            
                                            // Show tooltip or feedback
                                            const tooltip = new bootstrap.Tooltip(this, {
                                                title: 'Tersalin!',
                                                trigger: 'manual'
                                            });
                                            tooltip.show();
                                            setTimeout(() => tooltip.hide(), 1000);
                                        });
                                    }, 100);
                                });
                    }
                    
                    // Initialize attendees management for edit mode
                    var attendeesListEdit = [];
                    var searchTimeoutEdit = null;
                    
                    // Function to update attendees display
                    function updateAttendeesDisplayEdit() {
                        var $display = $('#attendees_display_edit');
                        var $hidden = $('#attendees_hidden_edit');
                        $display.empty();
                        $hidden.empty();
                        
                        attendeesListEdit.forEach(function(attendee, index) {
                            var badgeClass = attendee.type === 'user' ? 'badge-info' : 'badge-success';
                            var typeLabel = attendee.type === 'user' ? 'User' : 'Unit Kerja';
                            var removeBtn = dtr.allow_edit ? `<button type="button" class="close ml-2" onclick="removeAttendeeEdit(${index})" style="font-size: 1.2em;">&times;</button>` : '';
                            $display.append(`
                                <span class="badge ${badgeClass} mr-2 mb-2 d-inline-flex align-items-center" style="font-size: 0.9em; padding: 0.5em 0.75em;">
                                    <span class="badge badge-light mr-1">${typeLabel}</span>
                                    ${attendee.text}
                                    ${removeBtn}
                                </span>
                            `);
                            $hidden.append(`<input type="hidden" name="attendees[]" value="${attendee.id}" />`);
                        });
                        
                        $('#attendees_count_edit').text('(Total: ' + attendeesListEdit.length + ')');
                    }
                    
                    // Function to remove attendee
                    window.removeAttendeeEdit = function(index) {
                        if (dtr.allow_edit) {
                            attendeesListEdit.splice(index, 1);
                            updateAttendeesDisplayEdit();
                        }
                    };
                    
                    // Pre-populate attendees if they exist
                    if (val.attendees && val.attendees.length > 0) {
                        attendeesListEdit = val.attendees.map(function(a) { return {id: a.id, text: a.text, type: a.type}; });
                        updateAttendeesDisplayEdit();
                    }
                    
                    // Autocomplete functionality
                    if (dtr.allow_edit) {
                        $('#attendees_search_edit').on('input', function() {
                            var query = $(this).val().trim();
                            var $autocomplete = $('#attendees_autocomplete_edit');
                            
                            clearTimeout(searchTimeoutEdit);
                            
                            if (query.length < 2) {
                                $autocomplete.hide().empty();
                                return;
                            }
                            
                            searchTimeoutEdit = setTimeout(function() {
                                $.ajax({
                                    url: base_url + '/search-attendees',
                                    dataType: 'json',
                                    type: 'GET',
                                    data: { q: query },
                                    success: function(data) {
                                        $autocomplete.empty();
                                        
                                        if (data.length === 0) {
                                            $autocomplete.append('<div class="list-group-item">Tidak ada hasil</div>');
                                        } else {
                                            data.forEach(function(item) {
                                                // Check if already added
                                                var isAdded = attendeesListEdit.some(function(a) { return a.id === item.id; });
                                                if (!isAdded) {
                                                    var typeLabel = item.type === 'user' ? '<span class="badge badge-info">User</span>' : '<span class="badge badge-success">Unit Kerja</span>';
                                                    var $item = $('<a href="#" class="list-group-item list-group-item-action"></a>')
                                                        .html(typeLabel + ' ' + item.text)
                                                        .data('attendee', item)
                                                        .on('click', function(e) {
                                                            e.preventDefault();
                                                            attendeesListEdit.push({
                                                                id: item.id,
                                                                text: item.text,
                                                                type: item.type
                                                            });
                                                            updateAttendeesDisplayEdit();
                                                            $('#attendees_search_edit').val('');
                                                            $autocomplete.hide().empty();
                                                        });
                                                    $autocomplete.append($item);
                                                }
                                            });
                                            
                                            if ($autocomplete.children().length === 0) {
                                                $autocomplete.append('<div class="list-group-item">Semua hasil sudah ditambahkan</div>');
                                            }
                                        }
                                        
                                        $autocomplete.show();
                                    },
                                    error: function() {
                                        $autocomplete.hide().empty();
                                    }
                                });
                            }, 250);
                        });
                        
                        // Hide autocomplete when clicking outside
                        $(document).on('click', function(e) {
                            if (!$(e.target).closest('#attendees_search_edit, #attendees_autocomplete_edit').length) {
                                $('#attendees_autocomplete_edit').hide();
                            }
                        });
                    }
                    
                    // Update toggle switch label on change
                    $('#is_use_zoom').on('change', function() {
                        $('#zoom_label').text($(this).is(':checked') ? 'Ya' : 'Tidak');
                    });
                    
                    // Custom fields event handlers
                    if (dtr.allow_edit) {
                        form.on('click', '#add_custom_field', function() {
                            const container = $('#custom_fields_container');
                            const newIndex = container.find('.custom-field-row').length;
                            container.append(createCustomFieldRow('', '', newIndex, true));
                        });

                        form.on('click', '.remove_custom_field', function() {
                            $(this).closest('.custom-field-row').remove();
                        });
                    }
                    
                    // Sync visible select with hidden select for admin users
                    if (dtr.isAdmin) {
                        $('#unit_kerja_select_edit').on('change', function() {
                            var selectedValue = $(this).val();
                            form.find("select[name='unit_kerja']").val(selectedValue);
                        });
                    }

                    $this.$modal.find('form').on('submit', function () {
                        var nama_rapat = form.find("input[name='nama_rapat']").val().trim(),
                            unit_kerja_select_visible = $('#unit_kerja_select_edit'),
                            unit_kerja_select_hidden = form.find("select[name='unit_kerja']"),
                            unit_kerja_input = form.find("input[name='unit_kerja']"),
                            unit_kerja = '',
                            data_tag_uk = '',
                            data_bg_class = '';
                        
                        // Get unit_kerja value: from visible select (admin) or hidden input (standard user)
                        if (dtr.isAdmin && unit_kerja_select_visible.length > 0) {
                            // Admin: get from visible select
                            unit_kerja = unit_kerja_select_visible.val();
                            var selected_uk_option = unit_kerja_select_visible.find('option:selected')[0];
                            if (selected_uk_option) {
                                data_tag_uk = selected_uk_option.dataset.uk || '';
                                data_bg_class = selected_uk_option.dataset.bgc || '';
                            }
                        } else if (unit_kerja_input.length > 0) {
                            // Standard user: get from hidden input
                            unit_kerja = unit_kerja_input.val();
                            // Get data from original options
                            var tempDiv = $('<div>').html(dtr.opsi_uk);
                            var selectedOption = tempDiv.find('option[value="' + unit_kerja + '"]');
                            if (selectedOption.length > 0) {
                                data_tag_uk = selectedOption.attr('data-uk') || '';
                                data_bg_class = selectedOption.attr('data-bgc') || '';
                            }
                        } else {
                            // Fallback to original value
                            unit_kerja = dtr.data_edit.unit_kerja;
                            data_tag_uk = dtr.data_edit.singkatan_unit_kerja || '';
                            data_bg_class = dtr.data_edit.class_bg || '';
                        }
                        
                        var ruang_rapat = form.find("select[name='ruang_rapat']").val(),
                            mulai_rapat = form.find("input[name='mulai_rapat']").val(),
                            akhir_rapat = form.find("input[name='akhir_rapat']").val(),
                            tanggal_mulai = form.find("input[name='tanggal_mulai']").val(),
                            tanggal_selesai = form.find("input[name='tanggal_selesai']").val(),
                            is_use_zoom = form.find("input[name='is_use_zoom']").is(":checked") ? '1' : '0',
                            nomor_wa = form.find("input[name='nomor_wa']").val(),
                            attendees = form.find("input[name='attendees[]']").map(function() { return $(this).val(); }).get(),
                            // Calculate jumlah_peserta from attendees count
                            jumlah_peserta = attendees.length,
                            // Set topik_rapat = nama_rapat
                            topik_rapat = nama_rapat;
                        
                        // Collect custom fields
                        var custom_fields = [];
                        form.find('.custom-field-row').each(function(index) {
                            var key = $(this).find('input[name="custom_field_key[]"]').val();
                            var value = $(this).find('input[name="custom_field_value[]"]').val();
                            if (key && value) {
                                custom_fields.push({key: key, value: value});
                            }
                        });
                        
                        if (nama_rapat !== null && nama_rapat.length != 0 && akhir_rapat != '' && mulai_rapat != '' && is_use_zoom != null) {
                            if (moment(mulai_rapat, 'hh:mm').isAfter(moment(akhir_rapat, 'hh:mm')) || moment(mulai_rapat, 'hh:mm').isSame(moment(akhir_rapat, 'hh:mm'))) {
                                Swal.fire({
                                    type: 'error',
                                    title: 'Error!',
                                    html: '<p style="font-size:smaller">Waktu mulai rapat tidak boleh sama atau lebih dari waktu selesai rapat. Terima kasih</p>'
                                })
                            } else {
                                console.log(calEvent);
                                Swal.fire({
                                    type: 'question',
                                    title: 'Konfirmasi Simpan Data',
                                    html: '<p style="font-size: smaller; font-weight: bold;">Simpan data (Buat Rapat) ?</p>',
                                    showCancelButton: true,
                                    confirmButtonText: '<i class="fa fa-check"></i> Ya, Simpan',
                                    showLoaderOnConfirm: true,
                                    preConfirm: () => {
                                        return new Promise((resolve, reject) => {
                                            $.ajax({
                                                url: base_url + '/edit-rapat',
                                                dataType: 'json',
                                                type: 'POST',
                                                data: {
                                                    _token: token,
                                                    data: {
                                                        nama_rapat: nama_rapat,
                                                        unit_kerja: unit_kerja,
                                                        ruang_rapat : ruang_rapat,
                                                        jumlah_peserta:jumlah_peserta,
                                                        topik_rapat: topik_rapat,
                                                        mulai_rapat: mulai_rapat,
                                                        akhir_rapat: akhir_rapat,
                                                        tanggal_mulai: tanggal_mulai,
                                                        tanggal_selesai: tanggal_selesai,
                                                        is_use_zoom: is_use_zoom,
                                                        nomor_wa: nomor_wa,
                                                        attendees: attendees,
                                                        custom_fields: custom_fields,
                                                        rapat: calEvent.id_rapat                                                  
                                                    }
                                                },
                                                success: function (data) {
                                                    resolve(data);
                                                },
                                                error: function (err) {
                                                    reject(err.responseText);
                                                }
                                            })
                                        }).then((suc) => {
                                            calEvent.title = mulai_rapat + ' ' + data_tag_uk + ': ' + nama_rapat;
                                            calEvent.is_zoom = is_use_zoom;
                                            $this.$calendarObj.fullCalendar('updateEvent', calEvent);                                        
                                            $("#my-event").modal('toggle');
                                            return suc;
                                        }).catch((err) => {
                                            Swal.showValidationMessage(
                                                `Request failed: ${err}`
                                            );
                                        })
                                    },
                                    allowOutsideClick: () => !Swal.isLoading()
                                }).then(function (val) { 
                                    if(val.value)                                                         
                                    {
                                        Swal.fire({
                                            type: 'success',
                                            title: 'Sukses!',
                                            html: `<p style="font-size: smaller">Berhasil memperbarui data rapat!</p>`
                                        });
                                    }
                                    
                                })
                                
                            }

                        } else {
                            Swal.fire({
                                type: 'error',
                                title: 'Error!',
                                html: '<p style="font-size:smaller">Isian dengan tanda <span style="color: red">*</span> harus terisi. Terima kasih</p>'
                            })
                        }                    
                        return false;
                    });
                })
            })
        })                
    },


    /* on select : nge klik tanggal kosong ... */
    CalendarApp.prototype.onSelect = function(start, end, allDay) {
        
        var $this = this;
        $("#judul_modal_rapat").next(".unit-kerja-subtitle").remove();
        $this.$modal.modal({
            backdrop: 'static'
        });       

        opsi_unit_kerja().then((val) => {
            let opsi_uk = '';
            for(let i=0; i<val.result.length; i++)
            {
                opsi_uk += `<option value='${val.result[i].id}' data-bgc="${val.result[i].class_bg}" data-uk="${val.result[i].singkatan}" ${val.result[i].id == val.uk_ses ? 'selected' : ''}>${val.result[i].nama}</option>`;
            }
            return {opsi_uk: opsi_uk, no_hp_ses: val.no_hp_ses};
        }).then((dtr) => { 
            opsi_ruang().then((ruang) => {
                let opsi_ruang_html = '';
                for (let i = 0; i < ruang.result.length; i++) {
                    opsi_ruang_html += `<option value='${ruang.result[i].nama_ruang}'>${ruang.result[i].nama_ruang}</option>`;
                }           
                     
                $("#judul_modal_rapat").html("Buat Rapat");
                let temp_start = moment(start),
                    temp_end = moment(end).subtract(1, 'days');
                
                var form = $("<form></form>");
                form.append("<div class='row'></div>");
                var $row = form.find(".row");
                $row.append(`<div class='col-md-12 mb-4'>
                    <div class='form-group'>
                        <label class='control-label'>Unit Kerja <span style="color:red">*</span></label>
                        <select class='form-control' name='unit_kerja'>
                            ${dtr.opsi_uk}
                        </select>
                    </div>
                </div>`)
                // Row 1: Agenda Rapat (Full width)
                $row.append(`<div class='col-md-12 mb-4'>
                    <div class='form-group'>
                        <label class='control-label'>Agenda Rapat <span style="color:red">*</span></label>
                        <input class='form-control' placeholder='Masukan agenda rapat' type='text' name='nama_rapat'/>
                    </div>
                </div>`);
                
                // Row 2: Ruang Rapat (Left) and Sewa Zoom (Right) - 2 columns
                $row.append(`<div class='col-md-6 mb-4'>
                    <div class='form-group'>
                        <label class='control-label'>Ruang Rapat <span style="color:red">*</span></label>
                        <select class='form-control' name='ruang_rapat' id='ruang_rapat2'>
                            <option value=''>Pilih Ruangan</option>
                            ${opsi_ruang_html}
                        </select>
                    </div>
                </div>`);
                $row.append(`<div class='col-md-6 mb-4'>
                    <div class='form-group'>
                        <label class='control-label'>Sewa Zoom Meeting <span style="color:red">*</span></label>
                        <div class="d-flex align-items-center" style="height: 34px;">
                            <label class="toggle-switch mb-0 mr-3">
                                <input type="checkbox" name="is_use_zoom" id="is_use_zoom" value="1">
                                <span class="toggle-slider"></span>
                            </label>
                            <span id="zoom_label" style="line-height: 34px;">Tidak</span>
                        </div>
                    </div>
                </div>`);
                
                 // Row 3: Tanggal, Mulai, Selesai - 4 columns (split dates into 2 each)
                 $row.append(`<div class='col-md-3 mb-4'>
                     <div class='form-group'>
                         <label class='control-label'>Tanggal Mulai <span style="color:red">*</span></label>
                         <input type="text" class="form-control datepicker-rapat" name="tanggal_mulai" value="${temp_start.format("DD-MM-YYYY")}" />
                     </div>
                 </div>`);
                 $row.append(`<div class='col-md-3 mb-4'>
                     <div class='form-group'>
                         <label class='control-label'>Tanggal Selesai <span style="color:red">*</span></label>
                         <input type="text" class="form-control datepicker-rapat" name="tanggal_selesai" value="${temp_end.format("DD-MM-YYYY")}" />
                     </div>
                 </div>`);
                 $row.append(`<div class='col-md-3 mb-4'>
                     <div class='form-group'>
                         <label class='control-label'>Mulai <span style="color:red">*</span></label>
                         <input type="time" name="mulai_rapat" class="form-control" value="07:30">
                     </div>
                 </div>`);
                 $row.append(`<div class='col-md-3 mb-4'>
                     <div class='form-group'>
                         <label class='control-label'>Selesai <span style="color:red">*</span></label>
                         <input type="time" name="akhir_rapat" class="form-control" value="10:00">
                     </div>
                 </div>`);
                
                $row.append(`<div class='col-md-12 mb-4' id='ruang_lainnya' style='display:none;'>
                    <div class='form-group'>
                        <label class='control-label'>Detil Ruang Lainnya <span style="color:red">*</span></label>
                        <input class='form-control' placeholder='Masukan Nama Ruang Rapat' type='text' name='ruang_lainnya' id='ruanglain'/>
                    </div>
                </div>`);
                
                $row.append(`<div class='col-md-12 mb-4' id="wa_container" style="display: none">
                    <div class='form-group'>
                        <label class='control-label'>Nomor WA PJ Rapat <span style="color:red">*</span></label>
                        <input class='form-control' placeholder='Masukan nomor wa (aktif)' value="${dtr.no_hp_ses ? dtr.no_hp_ses : ''}" type='number' name='nomor_wa'/>
                        <div class="d-flex justify-content-start">
                            <small id="name1" class="badge badge-default badge-info form-text text-white"><i class="mdi mdi-information-outline"></i> Isikan nomor wa untuk menerima pesan id dan link rapat zooom</small>
                        </div>
                    </div>
                </div>`);
                
                $row.append(`<div class='col-md-12 mb-4'><hr style="border-top: 1px solid rgba(0, 0, 0, 0.2);">
                    <div class='form-group' style='position: relative;'>
                        <label class='control-label' data-base-label="Peserta Rapat">Peserta Rapat <span class="badge badge-info" id="attendees_count">(Total: 0)</span></label>
                        <input type='text' class='form-control' id='attendees_search' placeholder='Cari peserta (User atau Unit Kerja)...' autocomplete='off' />
                        <small class="form-text text-muted">Ketik minimal 2 karakter untuk mencari</small>
                        <div id='attendees_autocomplete' class='list-group' style='position: absolute; z-index: 1000; max-height: 200px; overflow-y: auto; display: none; width: calc(100% - 30px); margin-top: 2px; border: 1px solid #ddd; border-radius: 4px;'></div>
                        <div id='attendees_display' class='mt-3' style='min-height: 40px;'></div>
                        <div id='attendees_hidden'></div>
                    </div>
                    <hr style="border-top: 1px solid rgba(0, 0, 0, 0.2);">
                </div>`);
                
                // Add custom fields section for create form
                $row.append(customFieldsHTML([], true));
                
                // Hidden unit_kerja select for form submission
                $row.append(`<div class='col-md-12' style="display:none;">
                    <select class='form-control' name='unit_kerja'>
                        ${dtr.opsi_uk}
                    </select>
                </div>`);
                $this.$modal.find('.delete-event').hide().end().find('.save-event').show().end().find('.modal-body').empty().prepend(form).end().find('.save-event').unbind('click').click(function () {
                    form.submit();
                });
                
                // Initialize attendees management for create mode
                var attendeesList = [];
                var searchTimeout = null;
                
                // Function to update attendees display
                function updateAttendeesDisplay() {
                    var $display = $('#attendees_display');
                    var $hidden = $('#attendees_hidden');
                    $display.empty();
                    $hidden.empty();
                    
                    attendeesList.forEach(function(attendee, index) {
                        var badgeClass = attendee.type === 'user' ? 'badge-info' : 'badge-success';
                        var typeLabel = attendee.type === 'user' ? 'User' : 'Unit Kerja';
                        $display.append(`
                            <span class="badge ${badgeClass} mr-2 mb-2 d-inline-flex align-items-center" style="font-size: 0.9em; padding: 0.5em 0.75em;">
                                <span class="badge badge-light mr-1">${typeLabel}</span>
                                ${attendee.text}
                                <button type="button" class="close ml-2" onclick="removeAttendee(${index})" style="font-size: 1.2em;">&times;</button>
                            </span>
                        `);
                        $hidden.append(`<input type="hidden" name="attendees[]" value="${attendee.id}" />`);
                    });
                    
                    $('#attendees_count').text('(Total: ' + attendeesList.length + ')');
                }
                
                // Function to remove attendee
                window.removeAttendee = function(index) {
                    attendeesList.splice(index, 1);
                    updateAttendeesDisplay();
                };
                
                // Autocomplete functionality
                $('#attendees_search').on('input', function() {
                    var query = $(this).val().trim();
                    var $autocomplete = $('#attendees_autocomplete');
                    
                    clearTimeout(searchTimeout);
                    
                    if (query.length < 2) {
                        $autocomplete.hide().empty();
                        return;
                    }
                    
                    searchTimeout = setTimeout(function() {
                        $.ajax({
                            url: base_url + '/search-attendees',
                            dataType: 'json',
                            type: 'GET',
                            data: { q: query },
                            success: function(data) {
                                $autocomplete.empty();
                                
                                if (data.length === 0) {
                                    $autocomplete.append('<div class="list-group-item">Tidak ada hasil</div>');
                                } else {
                                    data.forEach(function(item) {
                                        // Check if already added
                                        var isAdded = attendeesList.some(function(a) { return a.id === item.id; });
                                        if (!isAdded) {
                                            var typeLabel = item.type === 'user' ? '<span class="badge badge-info">User</span>' : '<span class="badge badge-success">Unit Kerja</span>';
                                            var $item = $('<a href="#" class="list-group-item list-group-item-action"></a>')
                                                .html(typeLabel + ' ' + item.text)
                                                .data('attendee', item)
                                                .on('click', function(e) {
                                                    e.preventDefault();
                                                    attendeesList.push({
                                                        id: item.id,
                                                        text: item.text,
                                                        type: item.type
                                                    });
                                                    updateAttendeesDisplay();
                                                    $('#attendees_search').val('');
                                                    $autocomplete.hide().empty();
                                                });
                                            $autocomplete.append($item);
                                        }
                                    });
                                    
                                    if ($autocomplete.children().length === 0) {
                                        $autocomplete.append('<div class="list-group-item">Semua hasil sudah ditambahkan</div>');
                                    }
                                }
                                
                                $autocomplete.show();
                            },
                            error: function() {
                                $autocomplete.hide().empty();
                            }
                        });
                    }, 250);
                });
                
                // Hide autocomplete when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('#attendees_search, #attendees_autocomplete').length) {
                        $('#attendees_autocomplete').hide();
                    }
                });
                
                // Update toggle switch label on change
                $('#is_use_zoom').on('change', function() {
                    $('#zoom_label').text($(this).is(':checked') ? 'Ya' : 'Tidak');
                });
                
                // Custom fields event handlers for create form
                 form.on('click', '#add_custom_field', function() {
                     const container = $('#custom_fields_container');
                     const newIndex = container.find('.custom-field-row').length;
                     container.append(createCustomFieldRow('', '', newIndex, true));
                 });

                 form.on('click', '.remove_custom_field', function() {
                     $(this).closest('.custom-field-row').remove();
                 });
                 
                 // Initialize datepicker for date fields in create form
                 $('.datepicker-rapat').datepicker({
                     autoclose: true,
                     todayHighlight: true,
                     format: 'dd-mm-yyyy'
                 });

                 $("#ruang_rapat2").on("change", function(){
                    if (this.value === "Ruang Lainnya") {
                        let otherValue = prompt("Nama Ruang Rapat:");
                        $("#ruanglain").val(otherValue);
                    $("#ruang_lainnya").slideDown();
                    //   $("#ruang_lainnya").show();
                    } else {
                    $("#ruang_lainnya").slideUp();
                    $("#ruanglain").val('');
                    }
                });
                $this.$modal.find('.share-event').hide();
                $this.$modal.find('form').on('submit', function () {

                    var nama_rapat = form.find("input[name='nama_rapat']").val().trim(),
                        unit_kerja = form.find("select[name='unit_kerja']").val(),
                        ruang_rapat = form.find("select[name='ruang_rapat']").val(),
                        mulai_rapat = form.find("input[name='mulai_rapat']").val(),
                        akhir_rapat = form.find("input[name='akhir_rapat']").val(),
                        is_use_zoom = form.find("input[name='is_use_zoom']").is(":checked") ? '1' : '0',
                        nomor_wa = form.find("input[name='nomor_wa']").val(),
                        attendees = form.find("input[name='attendees[]']").map(function() { return $(this).val(); }).get(),
                        // Calculate jumlah_peserta from attendees count
                        jumlah_peserta = attendees.length,
                        // Set topik_rapat = nama_rapat
                        topik_rapat = nama_rapat,
                        data_tag_uk = form.find("select[name='unit_kerja'] option:selected")[0].dataset.uk,
                        data_bg_class = form.find("select[name='unit_kerja'] option:selected")[0].dataset.bgc;
                    
                    // Collect custom fields
                    var custom_fields = [];
                    form.find('.custom-field-row').each(function(index) {
                        var key = $(this).find('input[name="custom_field_key[]"]').val();
                        var value = $(this).find('input[name="custom_field_value[]"]').val();
                        if (key && value) {
                            custom_fields.push({key: key, value: value});
                        }
                    });

                    if(ruang_rapat=='Ruang Lainnya'){
                        ruang_rapat=form.find("input[name='ruang_lainnya']").val().trim()
                    }
                    
                    if (ruang_rapat !== null && ruang_rapat.length != 0 && nama_rapat !== null && nama_rapat.length != 0 && akhir_rapat != '' && mulai_rapat != '' && is_use_zoom != null) {
                        if (moment(mulai_rapat, 'hh:mm').isAfter(moment(akhir_rapat, 'hh:mm')) || moment(mulai_rapat, 'hh:mm').isSame(moment(akhir_rapat, 'hh:mm'))) {
                            Swal.fire({
                                type: 'error',
                                title: 'Error!',
                                html: '<p style="font-size:smaller">Waktu mulai rapat tidak boleh sama atau lebih dari waktu selesai rapat. Terima kasih</p>'
                            })
                        } else {
                            Swal.fire({
                                type: 'question',
                                title: 'Konfirmasi Simpan Data',
                                html: '<p style="font-size: smaller; font-weight: bold;">Simpan data (Buat Rapat) ?</p>',
                                showCancelButton: true,
                                confirmButtonText: '<i class="fa fa-check"></i> Ya, Simpan',
                                showLoaderOnConfirm: true,
                                preConfirm: () => {
                                    return new Promise((resolve, reject) => {
                                        $.ajax({
                                            url: base_url + '/tambah-rapat',
                                            dataType: 'json',
                                            type: 'POST',
                                            data: {
                                                _token: token,
                                                data: {
                                                    nama_rapat: nama_rapat,
                                                    unit_kerja: unit_kerja,
                                                    ruang_rapat: ruang_rapat,
                                                    jumlah_peserta: jumlah_peserta,
                                                    topik_rapat: topik_rapat,
                                                    mulai_rapat: mulai_rapat,
                                                    akhir_rapat: akhir_rapat,
                                                    is_use_zoom: is_use_zoom,
                                                    nomor_wa: nomor_wa,
                                                    attendees: attendees,
                                                     custom_fields: custom_fields,
                                                     tanggal_mulai: moment(form.find("input[name='tanggal_mulai']").val(), "DD-MM-YYYY").format("YYYY-MM-DD"),
                                                     tanggal_selesai: moment(form.find("input[name='tanggal_selesai']").val(), "DD-MM-YYYY").format("YYYY-MM-DD")
                                                }
                                            },
                                            success: function (data) {
                                                resolve(data);
                                            },
                                            error: function (err) {
                                                reject(err.responseText);
                                            }
                                        })
                                    }).then((suc) => {
                                         $this.$calendarObj.fullCalendar('renderEvent', {
                                             id_rapat: suc.rapat,
                                             title: mulai_rapat+' '+data_tag_uk + ': ' + nama_rapat,
                                             start: moment(form.find("input[name='tanggal_mulai']").val(), "DD-MM-YYYY").format("YYYY-MM-DD"),
                                             end: moment(form.find("input[name='tanggal_selesai']").val(), "DD-MM-YYYY").add(1, 'days').format("YYYY-MM-DD"),
                                             allDay: true,
                                             className: 'bg-' + data_bg_class,
                                             is_zoom: is_use_zoom,
                                             allow_edit: true,
                                             editable: true,
                                             waktu_mulai: mulai_rapat,
                                             waktu_selesai: akhir_rapat,
                                             ruang_rapat: ruang_rapat
                                         }, true);
                                        $("#my-event").modal('toggle');                                    
                                        return suc;
                                    }).catch((err) => {
                                        Swal.showValidationMessage(
                                            `Gagal membuat rapat: ${err}`
                                        );
                                        return false;
                                    })
                                },
                                allowOutsideClick: () => !Swal.isLoading()
                            }).then(function(val) {
                                // console.log(val);
                                // Only show success message if the operation was successful
                                if (val.value) {
                                    Swal.fire({
                                        type: 'success',
                                        title: 'Sukses!',
                                        html: `<p style="font-size: smaller">Berhasil menambah data rapat!</p>`
                                    });
                                }
                            })

                            // console.log(moment(start).format(), moment(end).subtract(1, 'days').format(), categoryClass);
                            // $this.$calendarObj.fullCalendar('renderEvent', {
                            //     title: data_tag_uk + ': ' + nama_rapat,
                            //     start: moment(start).format() + 'T' + mulai_rapat,
                            //     end: end,
                            //     allDay: false,
                            //     className: categoryClass,
                            //     dataCalendarRapat: 'siap'
                            // }, true);
                            // $this.$modal.modal('hide');
                        }

                    } else {
                        Swal.fire({
                            type: 'error',
                            title: 'Error!',
                            html: '<p style="font-size:smaller">Isian dengan tanda <span style="color: red">*</span> harus terisi. Terima kasih</p>'
                        })
                    }
                    return false;

                });
                $this.$calendarObj.fullCalendar('unselect');
            });
        });
        
    },

    CalendarApp.prototype.enableDrag = function() {
        //init events
        $(this.$event).each(function() {
            
            // create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
            // it doesn't need to have a start or end
            var eventObject = {
                title: $.trim($(this).text()) // use the element's text as the event title
            };
            // store the Event Object in the DOM element so we can get to it later
            $(this).data('eventObject', eventObject);
            // make the event draggable using jQuery UI
            $(this).draggable({
                zIndex: 999,
                revert: true, // will cause the event to go back to its
                revertDuration: 0 //  original position after the drag
            });
        });
    }
    
    /* Initializing */
    // console.log(new Date($.now()));
    CalendarApp.prototype.init = function() {
        this.enableDrag();
        
        var $this = this;
        var cachedUkSes = null;
        var cachedLvlSes = null;
        
        $this.$calendarObj = $this.$calendar.fullCalendar({
            slotDuration: '00:15:00',
            /* If we want to split day time each 15minutes */
            minTime: '08:00:00',
            maxTime: '19:00:00',
            defaultView: 'month',
            handleWindowResize: true,
            selectLongPressDelay: 1000,
            header: {
                left: 'prev,next today',
                center: 'title',
                // right: 'month,agendaWeek,agendaDay'                        
            },
            
            // Dynamic event loading based on calendar view range
            events: function(start, end, timezone, callback) {
                $.ajax({
                    url: base_url + '/get-data-rapat',
                    dataType: 'json',
                    type: 'GET',
                    data: {
                        start: start.format('YYYY-MM-DD'),
                        end: end.format('YYYY-MM-DD')
                    },
                    success: function(data) {
                         // Cache session data from response
                         if (cachedUkSes === null) {
                             cachedUkSes = data.uk_ses;
                             cachedLvlSes = data.lvl_ses;
                         }
                         
                         var events = [];
                         for (var i = 0; i < data.result.length; i++) {
                             var item = data.result[i];
                             var allowEdit = (cachedLvlSes == '2') || 
                                 (Array.isArray(cachedUkSes) && cachedUkSes.includes(parseInt(item.unit_kerja)));
                             
                             events.push({
                                 id_rapat: item.id,
                                 title: item.waktu_mulai_rapat + ' ' + item.singkatan_unit_kerja + ': ' + item.nama,
                                 start: item.tanggal_rapat_start + 'T' + item.waktu_mulai_rapat,
                                 end: moment(item.tanggal_rapat_end).add(1, 'days').format("YYYY-MM-DD"),
                                 className: 'bg-' + item.class_bg,
                                 allDay: true,
                                 is_zoom: (item.use_zoom == '1'),
                                 allow_edit: allowEdit,
                                 editable: allowEdit,
                                 waktu_mulai: item.waktu_mulai_rapat,
                                 waktu_selesai: item.waktu_selesai_rapat,
                                 ruang_rapat: item.ruang_rapat
                             });
                         }
                         
                         // Update legend with raw data (contains nama_unit_kerja)
                         updateCalendarLegend(data.result);
                         
                         callback(events);
                     },
                     error: function(err) {
                         console.error('Failed to load calendar events:', err);
                         $('#calendar-events').html('<div class="text-muted small text-danger">Gagal memuat data</div>');
                         callback([]);
                     }
                });
            },
            
            loading: function(isLoading) {
                if (isLoading) {
                    $("#spin-calendar").show();
                } else {
                    $("#spin-calendar").hide();
                }
            },
            
            // resizable: true,
            editable: true,
            droppable: true, // this allows things to be dropped onto the calendar !!!
            eventLimit: true, // allow "more" link when too many events
            selectable: true,
            drop: function (date) {
                $this.onDrop($(this), date);
            },
            select: function (start, end, allDay) {
                $this.onSelect(start, end, allDay);
            },
            eventClick: function (calEvent, jsEvent, view) {
                $this.onEventClick(calEvent, jsEvent, view);
            },
            eventDrop: function (info) {
                $this.onDragDrop(info);
            },
            eventRender: function (event, element) {
                if (event.is_zoom) {
                    element.find(".fc-title").prepend(`<i class="fas fa-video"></i> `);
                }
            },
            eventResize: function (info) {
                // console.log(moment(info.start).format());
                // console.log(moment(info.end).subtract(1, 'days').format());                        
                $.ajax({
                    url: base_url+'/edit-tangal-rapat-resize',
                    dataType: 'json',
                    type: 'POST',
                    data: {_token: token, data: {
                        rapat: info.id_rapat, 
                        ruang_rapat: info.ruang_rapat,
                        tanggal_rapat_start: moment(info.start).format("YYYY-MM-DD"), 
                        tanggal_rapat_end: moment(info.end).subtract(1, 'days').format("YYYY-MM-DD"),
                        mulai_rapat: info.waktu_mulai,
                        akhir_rapat: info.waktu_selesai
                    }},
                   success: function(data) {
                       console.log('sukses');
                           toastr.success('Berhasil merubah tanggal rapat.', 'Yeay!', { "showMethod": "fadeIn", "hideMethod": "fadeOut", timeOut: 2000 });
                   },
                   error: function(err) {
                       console.log(err.responseText);
                       toastr.error('Gagal merubah tanggal rapat.', 'Wadooh!', { "showMethod": "fadeIn", "hideMethod": "fadeOut", timeOut: 2000 });
                   }
               })
            },
            eventAllow: function (info, draggedEvent) {
                // console.log(dropInfo, draggedEvent)
                return draggedEvent.allow_edit;
                // console.log(moment(info.start).format());
                // console.log(moment(info.end).subtract(1, 'days').format());  
            }

        });

        //on new event
        this.$saveCategoryBtn.on('click', function () {
            var categoryName = $this.$categoryForm.find("input[name='category-name']").val();
            var categoryColor = $this.$categoryForm.find("select[name='category-color']").val();
            if (categoryName !== null && categoryName.length != 0) {
                $this.$extEvents.append('<div class="calendar-events m-b-20" data-class="bg-' + categoryColor + '" style="position: relative;"><i class="fa fa-circle text-' + categoryColor + ' m-r-10" ></i>' + categoryName + '</div>')
                $this.enableDrag();
            }

        });
    },

    //init CalendarApp
    $.CalendarApp = new CalendarApp, $.CalendarApp.Constructor = CalendarApp

}(window.jQuery),

//initializing CalendarApp
$(window).on('load', function() {

    $.CalendarApp.init()    
});



    
