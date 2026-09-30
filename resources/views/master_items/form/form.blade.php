<form method="POST" enctype="multipart/form-data">
    @csrf
    @if($method == 'edit')
        <div class="form-group mb-3">
            <label class="form-label">Kode Barang</label>
            <input type="text" class="form-control" name="kode_barang" required readonly value="{{$item->kode ?? ''}}">
        </div>
    @endif

    <div class="form-group mb-3">
        <label class="form-label">Nama</label>
        <input type="text" class="form-control" name="nama" required value="{{ old('nama', $item->nama ?? '') }}">
    </div>

    <div class="form-group mb-3">
        <label class="form-label">Kategori</label>
        <div class="border rounded p-2" style="max-height: 160px; overflow-y: auto; background-color: #fff;">
            @if(isset($categories) && count($categories) > 0)
                @foreach($categories as $cat)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $cat->id }}" id="cat_{{ $cat->id }}"
                            {{ in_array($cat->id, old('categories', $selectedCategories ?? [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="cat_{{ $cat->id }}">
                            {{ $cat->kode }} - {{ $cat->nama }}
                        </label>
                    </div>
                @endforeach
            @else
                <span class="text-muted">Belum ada kategori. <a href="{{ url('categories/form/new') }}" target="_blank">Tambah Kategori</a></span>
            @endif
        </div>
        <small class="text-muted">Pilih satu atau lebih kategori yang sesuai.</small>
    </div>

    <div class="form-group mb-3">
        <label class="form-label">Harga Beli</label>
        <input type="number" class="form-control" name="harga_beli" required value="{{ old('harga_beli', $item->harga_beli ?? '') }}">
    </div>

    <div class="form-group mb-3">
        <label class="form-label">Laba (dalam persen)</label>
        <input type="number" class="form-control" name="laba" required value="{{ old('laba', $item->laba ?? '') }}">
    </div>

    @php $selected = old('supplier', $item->supplier ?? ''); @endphp
    <div class="form-group mb-3">
        <label class="form-label">Supplier</label>
        <select class="form-control" required name="supplier">
            <option @if($selected == '') selected @endif value="">--Pilih--</option>
            <option @if($selected == 'Tokopaedi') selected @endif>Tokopaedi</option>
            <option @if($selected == 'Bukulapuk') selected @endif>Bukulapuk</option>
            <option @if($selected == 'TokoBagas') selected @endif>TokoBagas</option>
            <option @if($selected == 'E Commurz') selected @endif>E Commurz</option>
            <option @if($selected == 'Blublu') selected @endif>Blublu</option>
        </select>
    </div>

    @php $selected = old('jenis', $item->jenis ?? ''); @endphp
    <div class="form-group mb-3">
        <label class="form-label">Jenis</label>
        <select class="form-control" required name="jenis">
            <option @if($selected == '') selected @endif value="">--Pilih--</option>
            <option @if($selected == 'Obat') selected @endif>Obat</option>
            <option @if($selected == 'Alkes') selected @endif>Alkes</option>
            <option @if($selected == 'Matkes') selected @endif>Matkes</option>
            <option @if($selected == 'Umum') selected @endif>Umum</option>
            <option @if($selected == 'ATK') selected @endif>ATK</option>
        </select>
    </div>

    <div class="form-group mb-3">
        <label for="foto" class="form-label">Foto Barang</label>
        <input type="file" name="foto" id="foto" class="form-control" accept="image/*">
        @if(!empty($item->foto))
            <div class="mt-2">
                <small class="text-muted d-block mb-1">Foto saat ini:</small>
                <img src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->nama ?? 'Foto' }}" width="150" class="img-thumbnail rounded">
            </div>
        @endif
    </div>

    <button type="submit" class="btn btn-primary mt-2">Submit</button>
</form>