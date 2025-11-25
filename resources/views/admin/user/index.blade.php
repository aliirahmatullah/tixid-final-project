@extends('templates.app')

@section('content')
    <div class="container mt-3">
        @if (Session::get('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
        @endif

        <div class="d-flex justify-content-end">
            <a href="{{ route('admin.users.export') }}" class="btn btn-primary me-2">Export (.xlsx)</a>
            <a href="{{route('admin.users.trash')}}" class="btn btn-secondary me-2">Data Sampah</a>
            <a href="{{ route('admin.users.create') }}" class="btn btn-success">Tambah Data</a>
        </div>

        <h5 class="mt-3">Data Pengguna & Staff</h5>

        @php
            // Mapping warna role
            $roleColors = [
                'admin' => 'danger',
                'staff' => 'primary',
                'user' => 'success'
            ];
        @endphp

        <table class="table table-bordered" id="tableUser">
            <tr class="text-center">
                <th>#</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>

        </table>
    </div>
@endsection
@push('script')
<script>
    $(function() {
        $('#tableUser').DataTable({
            processing: true, //tanda load awal pas lg proses data
            serverSide: true, // data di proses di belakang (controller)
            ajax: "{{ route('admin.users.datatables') }}", // memanggil route
            columns: [ //urutan <td>
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name', orderable: true, searchable: true },
                { data: 'email', name: 'email', orderable: false, searchable: true },
                { data: 'role', name: 'role', orderable: true, searchable: true },
                { data: 'btnActions', name: 'btnActions', orderable: false, searchable: false },
            ]
        });
    })
</script>
@endpush
