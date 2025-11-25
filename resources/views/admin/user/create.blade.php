@extends('templates.app')
@section('content')
    <div class="w-75 d-block mx-auto my-5 p-4">
        <h5 class="text-center my-3">Tambah Data Staff</h5>
        <form method="POST" action="{{route('admin.users.store')}}">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Nama</label>
                <input type="text" id="name" class="form-control @error('name') is-invalid @enderror" name="name">
                @error('name')
                    <small class="text-danger">{{$message}}</small>
                @enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" class="form-control @error('email') is-invalid @enderror" name="email">
                @error('email')
                    <small class="text-danger">{{$message}}</small>
                @enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password">
                @error('password')
                    <small class="text-danger">{{$message}}</small>
                @enderror
            </div>
            <button class="btn btn-primary" type="submit">Tambah Data</button>
        </form>
    </div>
@endsection
