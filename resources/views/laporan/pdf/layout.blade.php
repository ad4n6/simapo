<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Laporan SIMAPO Apotek Bintan 88')</title>
    <style>
        @page {
            margin: 1.2cm 1.5cm;
        }
        * {
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        body {
            font-size: 10px;
            color: #1e293b;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 16px;
            color: #0f766e;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 2px 0;
            font-size: 9px;
            color: #64748b;
        }
        .title-block {
            text-align: center;
            margin-bottom: 15px;
        }
        .title-block h2 {
            font-size: 13px;
            margin: 0;
            color: #0f172a;
            text-transform: uppercase;
        }
        .title-block span {
            font-size: 9px;
            color: #64748b;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            font-size: 9px;
        }
        table.data-table th {
            background-color: #f8fafc;
            color: #334155;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: monospace; }
        .footer {
            margin-top: 25px;
            width: 100%;
        }
        .signature {
            float: right;
            width: 200px;
            text-align: center;
            font-size: 10px;
        }
        .signature-space {
            height: 50px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>APOTEK BINTAN 88</h1>
        <p>Jl. Bintan No. 88, Tanjungpinang, Kepulauan Riau | Telp: (0771) 888-888 | WA: 0812-3456-7890</p>
        <p>SIA: 503/SIA-088/DPMPTSP/2024 | SIPA: 19890515/SIPA_2172/2024/2001 (apt. Hendra Wijaya, S.Farm.)</p>
    </div>

    @yield('content')

    <div class="footer">
        <div class="signature">
            <p>Tanjungpinang, {{ date('d F Y') }}<br>Apoteker Penanggung Jawab,</p>
            <div class="signature-space"></div>
            <p class="font-bold"><u>apt. Hendra Wijaya, S.Farm.</u><br><span style="font-weight: normal; font-size: 8px;">SIPA: 19890515/SIPA_2172/2024/2001</span></p>
        </div>
    </div>

</body>
</html>
