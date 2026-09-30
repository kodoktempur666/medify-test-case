<form method="POST">
    @csrf
    <div class="form-group mb-3">
        <label class="form-label">Kode Kategori</label>
        <input type="text" class="form-control" name="kode" value="{{ old('kode', $category->kode ?? '') }}" placeholder="Otomatis dibuat jika dikosongkan" {{ $method == 'edit' ? 'readonly' : '' }}>
    </div>

    <div class="form-group mb-3">
        <label class="form-label">Nama Kategori</label>
        <input type="text" class="form-control" name="nama" required value="{{ old('nama', $category->nama ?? '') }}">
    </div>

    <button type="submit" class="btn btn-primary mt-2">Submit</button>
</form>