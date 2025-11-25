@extends('templates.app')
@section('content')
    <div class="container my-3">
        <div class="d-flex justify-content-end">
           <a href="{{route('admin.cinemas.index')}}" class="btn btn-secondary">Kembali</a>
        </div>
    <h3 class="my-3">Data Sampah Lokasi Bioskop</h3>
    @if (Session::get('success'))
    <div class="alert alert-success">{{Session::get('success')}}</div>
    @endif
    <table class="table table-bordered">
        <tr class="text-center">
            <th>#</th>
            <th>Nama Bioskop</th>
            <th>Lokasi</th>
            <th>Aksi</th>
        </tr>

        @foreach ($cinemas as $key => $item)
            <tr class="text-center">
                <td>{{ $key + 1 }}</td>
                {{-- mengambil relasi $item['namarelasi']['data']--}}
                <td>{{ $item['name'] ?? '-' }}</td>
                <td>{{ $item['location'] ?? '-' }}</td>
                <td class="d-flex justify-content-center">
                    <form action="{{route('admin.cinemas.restore', $item['id'])}}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-primary ms-2">Kembalikan</button>
                    </form>
                    <form action="{{route('admin.cinemas.delete_permanent', $item['id'])}}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger ms-2">Hapus Permanen</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>
    </div>
@endsection
