@extends('templates.app')
@section('content')
    <div class="w-75 d-block mx-auto my-5 p-4">
        <h5 class="text-center my-3">Edit Data Petugas</h5>
        <form method="POST" action="{{ route('admin.users.update', $user['id']) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Nama</label>
                <input type="text" id="name"
                       class="form-control @error('name') is-invalid @enderror"
                       name="name" value="{{ old('name', $user['name']) }}">
                @error('name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email"
                       class="form-control @error('email') is-invalid @enderror"
                       name="email" value="{{ old('email', $user['email']) }}">
                @error('email')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password (opsional)</label>
                <input type="password" id="password" class="form-control" name="password">
                <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
            </div>

            <button class="btn btn-primary" type="submit">Update Data</button>
        </form>
    </div>
@endsection
