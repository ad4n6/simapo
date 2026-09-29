<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Pemusnahan Obat - {{ $pemusnahan->no_pemusnahan }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111827;
            font-size: 12pt;
            line-height: 1.5;
            padding: 20mm;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 15pt;
            margin: 0;
            font-weight: bold;
        }
        .header p {
            font-size: 9pt;
            margin: 2px 0;
        }
        .title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 16px;
            margin-bottom: 4px;
        }
        .subtitle {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 20px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
            font-size: 10pt;
        }
        table.data th, table.data td {
            border: 1px solid #333;
            padding: 6px 8px;
        }
        table.data th {
            background-color: #f3f4f6;
            text-align: left;
        }
        .signatures {
            margin-top: 40px;
            width: 100%;
            display: flex;
            justify-content: space-between;
        }
        .sig-box {
            width: 45%;
            text-align: center;
            font-size: 10pt;
        }
        .sig-space {
            height: 70px;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #059669; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">
            Cetak Dokumen (Print)
        </button>
    </div>

    <div class="header">
        <h1>{{ strtoupper($apotek['nama']) }}</h1>
        <p>{{ $apotek['alamat'] }}</p>
        <p>Surat Izin Apotek (SIA): {{ $apotek['sia'] }} | SIPA: {{ $apotek['sipa'] }}</p>
    </div>

    <div class="title">BERITA ACARA PEMUSNAHAN OBAT KEDALUWARSA / RUSAK</div>
    <div class="subtitle">Nomor: {{ $pemusnahan->no_pemusnahan }}</div>

    <p style="text-align: justify; font-size: 10pt;">
        Pada hari ini, <strong>{{ $pemusnahan->tanggal->isoFormat('dddd') }}</strong> tanggal <strong>{{ $pemusnahan->tanggal->isoFormat('D MMMM Y') }}</strong>, bertempat di {{ $apotek['nama'] }}, kami yang bertanda tangan di bawah ini telah melakukan pemusnahan sediaan farmasi yang telah kedaluwarsa atau rusak dengan rincian sebagai berikut:
    </p>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Nama Obat / Sediaan</th>
                <th style="width: 20%;">Nomor Batch</th>
                <th style="width: 20%;">Tanggal Kedaluwarsa</th>
                <th style="width: 20%; text-align: right;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">1</td>
                <td><strong>{{ $pemusnahan->obat?->nama }}</strong><br><small>{{ $pemusnahan->obat?->nama_generik }}</small></td>
                <td>{{ $pemusnahan->batchObat?->no_batch }}</td>
                <td>{{ $pemusnahan->batchObat?->tanggal_kedaluwarsa->format('d/m/Y') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ $pemusnahan->jumlah }} {{ $pemusnahan->obat?->satuan?->nama }}</td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; font-size: 10pt; margin-top: 10px;">
        <tr>
            <td style="width: 25%; font-weight: bold;">Alasan Pemusnahan</td>
            <td style="width: 75%;">: {{ $pemusnahan->alasan }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Metode Pemusnahan</td>
            <td>: {{ $pemusnahan->metode_pemusnahan }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Saksi Pemusnahan</td>
            <td>: {{ $pemusnahan->saksi }}</td>
        </tr>
    </table>

    <p style="text-align: justify; font-size: 10pt; margin-top: 15px;">
        Demikian Berita Acara ini kami buat dengan sebenarnya dalam rangkap secukupnya untuk dapat dipergunakan sebagaimana mestinya sesuai ketentuan peraturan perundang-undangan kefarmasian yang berlaku.
    </p>

    <div style="margin-top: 40px; display: table; width: 100%;">
        <div style="display: table-cell; width: 50%; text-align: center; font-size: 10pt;">
            Saksi Pemusnahan,
            <div class="sig-space"></div>
            <strong><u>{{ $pemusnahan->saksi }}</u></strong>
        </div>
        <div style="display: table-cell; width: 50%; text-align: center; font-size: 10pt;">
            Bintan, {{ $pemusnahan->tanggal->isoFormat('D MMMM Y') }}<br>
            Apoteker Penanggung Jawab,
            <div class="sig-space"></div>
            <strong><u>{{ $apotek['pj'] }}</u></strong><br>
            <span>SIPA: {{ $apotek['sipa'] }}</span>
        </div>
    </div>

</body>
</html>
