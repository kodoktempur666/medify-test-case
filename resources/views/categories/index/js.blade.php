<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#table-categories').DataTable({
            searching: false,
            order: [[0, 'desc']]
        });
        getData();
    });

    $('.btn-get-data').click(function() {
        getData();
    });

    function getData() {
        $('#loading-filter').show();
        var table = $('#table-categories').DataTable();
        var filter_kode = $('#filter-kode').val() || '';
        var filter_nama = $('#filter-nama').val() || '';
        table.clear().draw();

        $.ajax({
            url: '{{ url("categories/search") }}',
            dataType: 'json',
            data: { kode: filter_kode, nama: filter_nama },
            success: function(results) {
                $.each(results.data, function(index, item) {
                    var btnAction = `<a href="{{ url('categories/view') }}/${item.id}" class="btn btn-sm btn-primary">View</a>`;
                    table.row.add([item.kode, item.nama, btnAction]);
                });
                table.draw(false);
                $('#loading-filter').hide();
            },
            error: function(xhr, textStatus, errorThrown) {
                alert('Terjadi kesalahan server, tidak dapat mengambil data');
                $('#loading-filter').hide();
            }
        });
    }
</script>