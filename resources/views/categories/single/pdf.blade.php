<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail Kategori - {{ $category->kode }}</title>
    <style>
        @page {
            margin: 20mm 15mm 25mm 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            color: #0d6efd;
            font-size: 18px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 6px 4px;
            font-size: 12px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        .data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        footer {
            position: fixed;
            bottom: -15mm;
            left: 0px;
            right: 0px;
            height: 15mm;
            font-size: 10px;
            text-align: right;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            color: #666;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>LAPORAN DETAIL KATEGORI</h2>
    </div>

    <!-- Informasi Kode dan Nama Kategori -->
    <table class="info-table">
        <tr>
            <td width="130"><strong>Kode Kategori</strong></td>
            <td width="10">:</td>
            <td>{{ $category->kode }}</td>
        </tr>
        <tr>
            <td><strong>Nama Kategori</strong></td>
            <td>:</td>
            <td>{{ $category->nama }}</td>
        </tr>
    </table>

    <h4 style="margin-bottom: 8px; margin-top: 15px;">Daftar Item dalam Kategori Ini:</h4>
    <!-- Informasi Item dalam bentuk tabel -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="30" class="text-center">No</th>
                <th>Kode Item</th>
                <th>Nama Item</th>
                <th>Jenis</th>
                <th>Harga Beli</th>
                <th>Harga Jual</th>
                <th>Supplier</th>
            </tr>
        </thead>
        <tbody>
            @forelse($category->masterItems as $index => $item)
                @php
                    $hargaJual = round($item->harga_beli + ($item->harga_beli * $item->laba / 100));
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->kode }}</td>
                    <td>{{ $item->nama }}</td>
                    <td>{{ $item->jenis }}</td>
                    <td>Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($hargaJual, 0, ',', '.') }}</td>
                    <td>{{ $item->supplier }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada item pada kategori ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer tanggal & waktu cetak -->
    <footer>
        Dicetak pada: {{ date('d-m-Y H:i:s') }}
    </footer>

</body>
</html>