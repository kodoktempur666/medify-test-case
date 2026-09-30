<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    /* Kontainer utama untuk foto */
    .foto-container {
        position: relative;
        width: 50px;
        height: 50px;
        cursor: pointer;
        overflow: hidden;
        border-radius: .25rem;
        /* Menyesuaikan border-radius img-thumbnail */
    }

    /* Memastikan gambar mengisi penuh kontainer */
    .foto-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .foto-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .foto-container:hover .foto-overlay {
        opacity: 1;
    }
</style>

<script>
    $(document).ready(function () {$('#table').DataTable({
            searching: false,
            order: [[0, 'desc']],
        });
        getData();
    });

    $('.btn-get-data').click(function () {
        getData();
    });

    function getData() {
        $('#loading-filter').show();
        var dataTableObj = $('#table').DataTable();
        var filter_kode = $('#filter-kode').val() || '';
        var filter_nama = $('#filter-nama').val() || '';
        var filter_kategori = $('#filter-kategori').val() || '';
        var filter_harga_min = $('#filter-harga-min').val() || '';
        var filter_harga_max = $('#filter-harga-max').val() || '';

        dataTableObj.clear().draw();

        var filter_params = $.param({
            kode: filter_kode,
            nama: filter_nama,
            category_id: filter_kategori,
            hargamin: filter_harga_min,
            hargamax: filter_harga_max
        });
        $('#btn-export-excel').attr('href', '{{url("master-items/export-excel")}}' + '?' + filter_params);

        $.ajax({
            url: '{{url("master-items/search")}}',
            dataType: 'json',
            tryCount: 0,
            retryLimit: 3,
            data: {
                kode: filter_kode,
                nama: filter_nama,
                category_id: filter_kategori,
                hargamin: filter_harga_min,
                hargamax: filter_harga_max
            },
            success: function (results) {
                var data = results.data;

                $.each(data, function (index, item) {
                    var harga_jual = item.harga_beli + (item.harga_beli * item.laba / 100);
                    harga_jual = Math.round(harga_jual);

                    // Formating Kategori Badge
                    var listKategori = '-';
                    if (item.categories && item.categories.length > 0) {
                        listKategori = item.categories.map(cat => `<span class="badge bg-secondary me-1">${cat.nama}</span>`).join(' ');
                    }

                    // Formating Foto
                    var fotoHtml = '<span class="text-muted">-</span>';
                    if (item.foto) {
                        var imgUrl = `{{ asset('storage') }}/` + item.foto;
                        fotoHtml = `
                            <div class="foto-container btn-preview-foto" data-url="${imgUrl}" title="Klik untuk memperbesar">
                                <img src="${imgUrl}" class="img-thumbnail" alt="${item.nama}">
                                <div class="foto-overlay">
                                    <i class="fas fa-eye"></i>
                                </div>
                            </div>
                        `;
                    }

                    // Tombol View
                    var btnView = `<a href="{{url('master-items/view/')}}/` + item.kode + `" class="btn btn-sm btn-primary">View</a>`;

                    // Susun tepat 9 kolom
                    var rowData = [
                        item.kode,          // Kolom 0
                        item.nama,          // Kolom 1
                        listKategori,       // Kolom 2: Kategori
                        item.jenis,         // Kolom 3
                        item.harga_beli,    // Kolom 4
                        harga_jual,         // Kolom 5
                        item.supplier,      // Kolom 6
                        fotoHtml,           // Kolom 7
                        btnView             // Kolom 8
                    ];

                    dataTableObj.row.add(rowData);
                });

                dataTableObj.draw(false);
                $('#loading-filter').hide();
            },
            error: function (xhr, textStatus, errorThrown) {
                this.tryCount++;
                if (this.tryCount <= this.retryLimit) {
                    $.ajax(this);
                    return;
                }
                alert('Terjadi kesalahan server, tidak dapat mengambil data');
                $('#loading-filter').hide();
            }
        });
    }

    $(document).on('click', '.btn-preview-foto', function () {
        var imageUrl = $(this).data('url');$('#imgModalPreview').attr('src', imageUrl);
        $('#modalFoto').modal('show');
    });
</script>