<div id="filter-container" class="mb-3">
    <h4>Filter</h4>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Kode</label>
                <input type="text" class="form-control" id="filter-kode">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Nama</label>
                <input type="text" class="form-control" id="filter-nama">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Kategori</label>
                <select class="form-control" id="filter-kategori">
                    <option value="">-- Semua Kategori --</option>
                    @if(isset($categories) && count($categories) > 0)
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nama }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Harga Min</label>
                <input type="number" class="form-control" id="filter-harga-min">
            </div>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-3">
            <div class="form-group">
                <label>Harga Max</label>
                <input type="number" class="form-control" id="filter-harga-max">
            </div>
        </div>
        <div class="col-md-9 d-flex align-items-end">
            <button class="btn btn-primary btn-get-data me-2">Filter</button>
            <span id="loading-filter" style="display: none;">Loading...</span>
        </div>
    </div>
</div>