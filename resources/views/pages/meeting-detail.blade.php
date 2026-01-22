<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $rapat->nama }} - MeetApp Kalsel</title>
    <link rel="shortcut icon" href="{{ url('public/image/meetappico.png') }}" type="image/png">
    
    <link href="{{ url('public/css/style.min.css')}}" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4e73df; /* Your MeetApp Blue */
            --primary-dark: #2e59d9;
            --bg-color: #f3f4f6;
            --text-dark: #2d3748;
            --text-muted: #718096;
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-dark);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        /* Center Layout */
        .main-wrapper {
            padding: 40px 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
        }

        .card-container {
            background: #fff;
            width: 100%;
            max-width: 900px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            overflow: hidden; /* Keeps the header inside rounded corners */
        }

        /* BRAND HEADER - The biggest visual fix */
        .header-banner {
            background: var(--primary-color);
            padding: 40px 32px;
            color: white !important;
        }

        .meeting-title {
            font-size: 32px;
            font-weight: 700;
            margin: 0 0 10px 0;
            line-height: 1.3;
            color: white !important;

        }

        .meeting-meta {
            font-size: 15px;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        /* CONTENT PADDING */
        .content-body {
            padding: 32px;
        }

        /* SECTIONS */
        .section-box {
            margin-bottom: 35px;
        }

        .section-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 12px;
            border-bottom: 2px solid #edf2f7;
            padding-bottom: 8px;
        }

        /* DATA GRID - Better than simple rows */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .info-item {
            background: #f8f9fc;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #dde2f1;
        }

        .info-item label {
            display: block;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .info-item span {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 16px;
        }

        /* ATTENDEES */
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            margin-right: 6px;
            margin-bottom: 8px;
        }
        .badge-primary { background: #e6f0ff; color: #0056b3; border: 1px solid #b8daff; }
        .badge-success { background: #e6fffa; color: #047481; border: 1px solid #b2f5ea; }

        /* ZOOM BOX - Stand out styling */
        .zoom-box {
            background: #fff;
            border: 1px solid #e3e6f0;
            border-radius: 12px;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .zoom-box::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 5px;
            background: #2d89ef; /* Zoom Blue */
        }
        .zoom-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
        }
        .zoom-row:last-child { border-bottom: none; }
        .zoom-link { color: #2d89ef; font-weight: bold; text-decoration: none; }
        .zoom-link:hover { text-decoration: underline; }

        /* DOWNLOAD BUTTON */
        .doc-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            border: 1px solid #d1d3e2;
            padding: 20px;
            border-radius: 10px;
            transition: all 0.2s;
        }
        .doc-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .btn-download-action {
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-download-action:hover { background: var(--primary-dark); }

        /* FOOTER */
        .footer-link {
            text-align: center;
            margin-top: 40px;
            font-size: 14px;
            color: var(--text-muted);
        }

        /* TOAST NOTIFICATION */
        .toast {
            visibility: hidden;
            min-width: 250px;
            background-color: #28a745;
            color: white;
            text-align: center;
            border-radius: 4px;
            padding: 10px;
            position: fixed;
            z-index: 1;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 14px;
            opacity: 0;
            transition: opacity 0.3s, visibility 0s 0.3s;
        }

        .toast.show {
            visibility: visible;
            opacity: 1;
            transition: opacity 0.3s;
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            .main-wrapper { padding: 0; }
            .card-container { border-radius: 0; box-shadow: none; }
            .header-banner { padding: 30px 20px; }
            .content-body { padding: 20px; }
            .info-grid { grid-template-columns: 1fr; }
            .doc-card { flex-direction: column; text-align: center; gap: 15px; }
        }

        .document-list {
    margin-top: 15px;
}

.document-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    margin-bottom: 10px;
    background: #fff;
    transition: all 0.2s;
}

.document-item:hover {
    border-color: #4e73df;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.document-icon {
    font-size: 24px;
    margin-right: 15px;
    width: 30px;
    text-align: center;
}

.document-details {
    flex: 1;
    min-width: 0; /* Prevents flex item from overflowing */
}

.document-name {
    font-weight: 600;
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.document-meta {
    font-size: 12px;
    color: #6c757d;
}

.btn-download {
    background: #4e73df;
    color: white;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 13px;
    text-decoration: none;
    display: flex;
    align-items: center;
    transition: background-color 0.2s;
    white-space: nowrap;
    margin-left: 10px;
}

.btn-download:hover {
    background: #2e59d9;
    color: white;
    text-decoration: none;
}

.btn-download i {
    margin-right: 5px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .document-item {
        flex-wrap: wrap;
        padding: 10px;
    }
    
    .document-details {
        flex-basis: 100%;
        margin-bottom: 8px;
    }
    
    .btn-download {
        margin-left: auto;
    }
}
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="card-container">
        
        <div class="header-banner">
            <h1 class="meeting-title">{{ $rapat->nama }}</h1> 
            
    
            <div class="meeting-meta">
                <span><strong>📅 
                    @if($rapat->tanggal_rapat_start == $rapat->tanggal_rapat_end)
                        {{ date('j M Y', strtotime($rapat->tanggal_rapat_start)) }}
                    @else
                        {{ date('j M', strtotime($rapat->tanggal_rapat_start)) }} - {{ date('j M Y', strtotime($rapat->tanggal_rapat_end)) }}
                    @endif
                </strong>
                </span>
                
                @if($rapat->waktu_mulai_rapat && $rapat->waktu_selesai_rapat)
                <span style="margin-left: 15px;">⏰ <strong>{{ substr($rapat->waktu_mulai_rapat, 0, 5) }} - {{ substr($rapat->waktu_selesai_rapat, 0, 5) }}</strong></span>
                @endif
            </div>
            <!--copy url share symbol-->
            
            <strong><button id="copy-url-link" class="btn-copy-url white-text text-bold btn-sm btn-success" style="color: white !important; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">
                <i class="fa fa-share-alt"></i> &nbsp;<span id="button-text">Bagikan Link Rapat</span>
            </button></strong>
            <div id="toast" class="toast">Link Tersalin!</div>
        </div>

        <div class="content-body">
            
            {{-- @if($rapat->topik)
            <div class="section-box">
                <div class="section-label">Topik Pembahasan</div>
                <div style="font-size: 18px; font-weight: 500;">{{ $rapat->topik }}</div>
            </div>
            @endif --}}

            <div class="section-box">
                <div class="section-label">Detail Rapat</div>
                <div class="info-grid">
                    <div class="info-item">
                        <label>🏢 Unit Kerja</label>
                        <span>{{ $rapat->nama_unit_kerja }}</span>
                    </div>
                    <div class="info-item">
                        <label>📍Ruang Rapat</label>
                        <span>{{ $rapat->ruang_rapat }}</span>
                    </div>
                    @if($rapat->jumlah_peserta)
                    <div class="info-item">
                        <label>👥 Estimasi Peserta</label>
                        <span>{{ $rapat->jumlah_peserta }} Orang</span>
                    </div>
                    @endif
                    @if($creator)
                    <div class="info-item">
                        <label>👤 Dibuat Oleh</label>
                        <span>{{ $creator->nama }}</span>
                    </div>
                    @endif
                </div>
            </div>

            @if($rapat->use_zoom == 1 && $zoomDetails && $zoomDetails->count() > 0)
            <div class="section-box">
                <div class="section-label" style="color:#2d89ef; border-color: #2d89ef;">Zoom Meeting</div>
                @foreach($zoomDetails as $zoom)
                <div class="zoom-box">
                    @if($zoom->zoom_link)
                    <div class="zoom-row">
                        <strong style="color:#2d3748">Link Meeting</strong>
                        <a href="{{ $zoom->zoom_link }}" target="_blank" class="zoom-link">Buka Zoom</a>
                    </div>
                    @endif
                    
                    @if($zoom->zoom_id)
                    <div class="zoom-row">
                        <span style="color:#718096">Meeting ID</span>
                        <span style="font-family: monospace; font-size: 1.1em;">{{ $zoom->zoom_id }}</span>
                    </div>
                    @endif
                    
                    @if($zoom->zoom_password)
                    <div class="zoom-row">
                        <span style="color:#718096">Passcode</span>
                        <span style="font-family: monospace; font-size: 1.1em;">{{ $zoom->zoom_password }}</span>
                    </div>
                    @endif

                    @if($zoom->nama_host)
                    <div class="zoom-row">
                        <span style="color:#718096">Host</span>
                        <span>{{ $zoom->nama_host }}</span>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            @if(!empty($attendees))
            <div class="section-box">
                <div class="section-label">Daftar Peserta</div>
                <div>
                    @foreach($attendees as $attendee)
                        <span class="badge {{ $attendee['type'] == 'user' ? 'badge-primary' : 'badge-success' }}">
                            {{ $attendee['text'] }}
                        </span>
                    @endforeach
                </div>
            </div>
            @else
            <div class="section-box">
                <div class="section-label">Daftar Peserta</div>
                <div>
                    <span class="badge badge-primary">Tidak Ada Daftar Peserta</span>
                </div>
            </div>
            @endif
{{-- 
            <div class="section-box">
                <div class="section-label">Notulensi & Dokumentasi</div>
                @if($hasDocumentation && $notulensi)
                    <div class="doc-card">
                        <div>
                            <div style="font-weight: bold; margin-bottom: 4px;">{{ $notulensi->nama_file }}</div>
                            <small style="color: #718096;">Diunggah: {{ date('j M Y, H:i', $notulensi->created_at) }}</small>
                        </div>
                        <a href="{{ url('/meeting/' . $rapat->uid . '/download') }}" class="btn-download-action">
                            <i class="fa fa-download"></i> Unduh File
                        </a>
                    </div>
                @else
                    <div style="background: #fafafa; padding: 15px; border-radius: 8px; text-align: center; color: #999; border: 1px dashed #ccc;">
                        Belum ada dokumen yang diunggah.
                    </div>
                @endif
            </div> --}}
            @if(!empty($notulensiFiles))
            <div class="section-box">
                <div class="section-label">Dokumen Rapat</div>
                <div class="document-list">
                    @foreach($notulensiFiles as $file)
                    <div class="document-item">
                        <div class="document-icon">
                            @php
                                $icon = 'fa-file';
                                switch(strtolower($file['extension'])) {
                                    case 'pdf': $icon = 'fa-file-pdf text-danger'; break;
                                    case 'doc':
                                    case 'docx': $icon = 'fa-file-word text-primary'; break;
                                    case 'xls':
                                    case 'xlsx': $icon = 'fa-file-excel text-success'; break;
                                    case 'jpg':
                                    case 'jpeg':
                                    case 'png':
                                    case 'gif': $icon = 'fa-file-image text-warning'; break;
                                    case 'zip':
                                    case 'rar': $icon = 'fa-file-archive text-muted'; break;
                                }
                            @endphp
                            <i class="fa {{ $icon }}"></i>
                        </div>
                        <div class="document-details">
                            <div class="document-name">{{ $file['name'] }}</div>
                            <div class="document-meta">
                                {{ number_format($file['size'] / 1024, 1) }} KB • 
                                {{ date('d M Y H:i', $file['created_at']) }}
                            </div>
                        </div>
                        <a href="{{ url($file['path']) }}" class="btn-download" download>
                            <i class="fas fa-download"></i> Unduh
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            <div class="footer-link">
                &copy; MeetApp Kalsel &bull; <a href="{{ url('/') }}" style="color: var(--primary-color); font-weight:600; text-decoration:none;">Kembali ke Meetapp</a>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const copyButton = document.getElementById('copy-url-link');
        const buttonText = document.getElementById('button-text');
        const toast = document.getElementById('toast');
        const meetingUrl = '{{ url("/meeting/" . $rapat->uid) }}';

        copyButton.addEventListener('click', function() {
            // Copy URL to clipboard
            navigator.clipboard.writeText(meetingUrl).then(function() {
                // Show toast
                toast.classList.add('show');
                
                // Change button text
                const originalText = buttonText.textContent;
                buttonText.textContent = 'Link Tersalin!';
                
                // Reset button text after 2 seconds
                setTimeout(function() {
                    buttonText.textContent = originalText;
                }, 2000);
                
                // Hide toast after 3 seconds
                setTimeout(function() {
                    toast.classList.remove('show');
                }, 3000);
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = meetingUrl;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    
                    // Show toast
                    toast.classList.add('show');
                    
                    // Change button text
                    const originalText = buttonText.textContent;
                    buttonText.textContent = 'Link Tersalin!';
                    
                    // Reset button text after 2 seconds
                    setTimeout(function() {
                        buttonText.textContent = originalText;
                    }, 2000);
                    
                    // Hide toast after 3 seconds
                    setTimeout(function() {
                        toast.classList.remove('show');
                    }, 3000);
                } catch (err) {
                    console.error('Fallback: Could not copy text: ', err);
                }
                document.body.removeChild(textArea);
            });
        });
    });
</script>
</body>
</html>