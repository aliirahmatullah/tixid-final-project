@extends('templates.app')
@section('content')
    <div class="container my-3">
        <div class="d-flex justify-content-end">
           <a href="{{route('admin.movies.index')}}" class="btn btn-secondary">Kembali</a>
        </div>
    <h3 class="my-3">Data Sampah Film</h3>
    @if (Session::get('success'))
    <div class="alert alert-success">{{Session::get('success')}}</div>
    @endif
    <table class="table table-bordered">
        <tr class="text-center">
            <th>#</th>
            <th>Poster</th>
            <th>Judul Film</th>
            <th>Status Aktif</th>
            <th>Aksi</th>
        </tr>

        @foreach ($movies as $key => $item)
            <tr class="text-center">
                <td>{{ $key + 1 }}</td>
                {{-- mengambil relasi $item['namarelasi']['data']--}}
                <td>
                    <img src="{{asset('storage/'.$item['poster'])}}" alt="" width="100">
                </td>
                <td>{{ $item['title'] ?? '-' }}</td>
                <td>
                        @if ($item['actived'] == 1)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-danger">Non-Aktif</span>
                        @endif
                </td>
                <td class="d-flex justify-content-center">
                    <form action="{{route('admin.movies.restore', $item['id'])}}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-primary ms-2">Kembalikan</button>
                    </form>
                    <form action="{{route('admin.movies.delete_permanent', $item['id'])}}" method="POST">
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
