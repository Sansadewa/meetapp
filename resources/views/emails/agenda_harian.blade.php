<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Rapat Hari Ini</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #333;
        }
        .meeting {
            border-left: 4px solid #0066cc;
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .meeting-number {
            display: inline-block;
            background-color: #0066cc;
            color: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            line-height: 28px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin-right: 10px;
        }
        .meeting-title {
            font-size: 16px;
            font-weight: bold;
            color: #0066cc;
            margin-bottom: 10px;
            display: inline-block;
        }
        .meeting-detail {
            font-size: 13px;
            margin-bottom: 6px;
            color: #555;
        }
        .meeting-detail strong {
            color: #333;
            min-width: 110px;
            display: inline-block;
        }
        .meeting-label {
            display: inline-block;
            min-width: 110px;
            font-weight: 600;
            color: #333;
        }
        .zoom-badge {
            display: inline-block;
            background-color: #e3f2fd;
            color: #0066cc;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 8px;
        }
        .link-button {
            display: inline-block;
            background-color: #0066cc;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            margin-top: 10px;
            font-weight: 600;
        }
        .link-button:hover {
            background-color: #0052a3;
        }
        .no-meetings {
            text-align: center;
            padding: 30px 20px;
            color: #666;
            font-size: 16px;
        }
        .footer {
            background-color: #f0f0f0;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
        }
        .divider {
            height: 1px;
            background-color: #eee;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📅 Agenda Rapat Anda</h1>
            <p>{{ $tanggal }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            @if(count($meetings) > 0)
                <div class="greeting">
                    Halo <strong>{{ $user->nama }}</strong>,
                </div>
                <p style="margin-bottom: 20px; color: #666;">
                    Anda memiliki <strong>{{ count($meetings) }} rapat</strong> hari ini. Berikut detail agendanya:
                </p>

                @foreach($meetings as $key => $meeting)
                    <div class="meeting">
                        <div>
                            <span class="meeting-number">{{ $key + 1 }}</span>
                            <span class="meeting-title">{{ $meeting->nama }}</span>
                        </div>
                        
                        <div style="margin-top: 10px;">
                            @if($meeting->topik)
                                <div class="meeting-detail">
                                    <span class="meeting-label">Topik:</span>
                                    {{ $meeting->topik }}
                                </div>
                            @endif

                            @if($meeting->nama_unit_kerja)
                                <div class="meeting-detail">
                                    <span class="meeting-label">Unit Kerja:</span>
                                    {{ $meeting->nama_unit_kerja }}
                                </div>
                            @endif

                            @if($meeting->ruang_rapat)
                                <div class="meeting-detail">
                                    <span class="meeting-label">Ruang:</span>
                                    {{ $meeting->ruang_rapat }}
                                </div>
                            @endif

                            @if($meeting->waktu_mulai_rapat)
                                <div class="meeting-detail">
                                    <span class="meeting-label">Waktu:</span>
                                    {{ $meeting->waktu_mulai_rapat }} - {{ $meeting->waktu_selesai_rapat }}
                                </div>
                            @endif

                            @if($meeting->zoom_id)
                                <div class="zoom-badge">✓ Menggunakan Zoom: Ya</div>
                            @endif

                            <a href="{{ config('app.url') }}/meeting/{{ $meeting->uid }}" class="link-button">
                                → Lihat Detail & Join
                            </a>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="no-meetings">
                    <h2 style="color: #0066cc; margin-bottom: 10px;">😊</h2>
                    <p>Tidak ada rapat hari ini.</p>
                    <p style="margin-top: 10px; font-size: 13px; color: #999;">Nikmati hari Anda dengan produktif!</p>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                <strong>MeetApp Kalsel</strong> - Sistem Manajemen Rapat<br>
                BPS Kalimantan Selatan<br>
                <small style="color: #999;">Email otomatis, harap tidak membalas. Akses agenda Anda di aplikasi MeetApp.</small>
            </p>
        </div>
    </div>
</body>
</html>
