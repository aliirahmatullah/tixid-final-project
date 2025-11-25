@extends('templates.app')
@section('content')
<div class="w-75 d-block mx-auto my-5 p-4">
    <h5 class="text-center my-5">Tambah Data Promo</h5>
    <form action="{{route('staff.promos.store')}}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="promo_code" class="form-label">Kode Promo</label>
            <input type="text" id="promo_code" class="form-control @error('promo_code') is-invalid @enderror" name="promo_code" name="promo_code">
            @error('promo_code')
                <small class="text-danger">{{$message}}</small>
            @enderror
        </div>
        <div class="mb-3">
            <label for="type" class="form-label">Tipe Promo</label>
            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror">
                <option value="">Pilih</option>
                <option value="percent">%</option>
                <option value="rupiah">Rupiah</option>
            </select>
            @error('type')
                <small class="text-danger">{{$message}}</small>
            @enderror
        </div>
        <div class="mb-3">
            <label for="discount" class="form-label">Jumlah Potongan</label>
            <input type="number" id="discount" class="form-control @error('discount') is-invalid @enderror" name="discount">
            @error('discount')
                <small class="text-danger">{{$message}}</small>
            @enderror
        </div>
        <button class="btn btn-primary" type="submit">Tambah Data</button>
        </div>
    </form>
</div>
@endsection