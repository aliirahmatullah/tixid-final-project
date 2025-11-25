@extends('templates.app')

@section('content')
<div class="container my-5">
    <h5 class="mb-5">Seluruh Film Sedang Tayang</h5>
    {{-- form untuk mencari gunakan get, action dikosongkan karna dia akan diarahkan ke halaman ini --}}
    <form action="" method="GET">
        @csrf
        <div class="row">
            <div class="col-10 mb-3 ">
                 <input type="text" name="search_movie" placeholder="Cari Judul Film..." class="form-control">
            </div>
            <div class="col-2">
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
        </div>
    </form>
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-4">
        @foreach ($movies as $movie)
            <div class="col">   
                <div class="card h-100">
                    <img src="{{ asset('storage/' . $movie['poster']) }}"
                         alt="{{ $movie['title'] }}"
                         class="card-img-top"
                         style="height: 355px; object-fit: cover;">
                    <div class="card-body p-2">
                        <p class="card-text text-center py-2 bg-light">
                            <a href="{{route('schedules.detail', $movie['id'])}}" class="text-warning"><b>Beli Tiket</b></a>
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@endsection