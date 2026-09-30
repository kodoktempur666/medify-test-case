@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="form-group mb-2">
                <a href="{{url('categories')}}" class="btn btn-secondary">Kembali ke Daftar Kategori</a>
            </div>
            <div class="card">
                <div class="card-header">Detail Kategori</div>

                <div class="card-body">
                    <table class="table table-borderless mb-3" style="max-width: 500px;">
                        <tr>
                            <th width="150">Kode Kategori</th>
                            <td width="20">:</td>
                            <td>{{$data->kode}}</td>
                        </tr>
                        <tr>
                            <th>Nama Kategori</th>
                            <td>:</td>
                            <td>{{$data->nama}}</td>
                        </tr>
                    </table>

                    <div class="mb-4">
                        <a class="btn btn-info me-1" href="{{url('categories/form/edit/' . $data->id)}}">Edit</a>
                        <a href="{{ url('categories/pdf-download/' . $data->id) }}" class="btn btn-danger me-1">
                            <i class="fas fa-file-pdf me-1"></i> Download PDF
                        </a>
                        <a class="btn btn-danger" href="{{url('categories/delete/' . $data->id)}}"
                            onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
                    </div>

                    <h5 class="fw-bold mt-4 mb-3">Daftar Item dalam Kategori Ini</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th>Kode Item</th>
                                    <th>Nama Item</th>
                                    <th>Jenis</th>
                                    <th>Harga Beli</th>
                                    <th>Harga Jual</th>
                                    <th>Supplier</th>
                                    <th width="70" class="text-center">Foto</th>
                                    <th width="80" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data->masterItems as $index => $item)
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
                                        <td class="text-center">
                                            @if($item->foto)
                                                <img src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->nama }}" width="45" height="45" class="img-thumbnail rounded" style="object-fit: cover;">
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ url('master-items/view/' . $item->kode) }}" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-3">Tidak ada item dalam kategori ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
@endsection