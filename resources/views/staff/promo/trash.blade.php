@extends('templates.app')
@section('content')
    <div class="container my-3">
        <div class="d-flex justify-content-end">
           <a href="{{route('staff.promos.index')}}" class="btn btn-secondary">Kembali</a>
        </div>
    <h3 class="my-3">Data Sampah Promo</h3>
    @if (Session::get('success'))
    <div class="alert alert-success">{{Session::get('success')}}</div>
    @endif
    <table class="table table-bordered">
        <tr class="text-center">
            <th>#</th>
            <th>Nama Promo</th>
            <th>Total Potongan</th>
            <th>Aksi</th>
        </tr>

        @foreach ($promos as $key => $item)
            <tr class="text-center">
                <td>{{ $key + 1 }}</td>
                <td>{{ $item['promo_code'] ?? '-' }}</td>
                <td>
                    @if($item->type == 'percent')
                    {{ $item->discount }} %
                    @else
                    Rp. {{ number_format($item->discount, 0, ',', '.') }}
                    @endif
                </td>

                <td class="d-flex justify-content-center">
                    <form action="{{route('staff.promos.restore', $item['id'])}}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-primary ms-2">Kembalikan</button>
                    </form>
                    <form action="{{route('staff.promos.delete_permanent', $item['id'])}}" method="POST">
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
