@extends('templates.app')
@section('content')
    <div class="container my-3">
        <div class="d-flex justify-content-end">
            <a href="{{route('staff.schedules.export')}}" class="btn btn-primary me-2">Export (.xlsx)</a>
            <a href="{{route('staff.schedules.trash')}}" class="btn btn-secondary me-2">Data Sampah</a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdd">Tambah Data</button>
        </div>

    <h3 class="my-3">Data Jadwal Tayangan</h3>
    @if (Session::get('success'))
        <div class="alert alert-success">{{Session::get('success')}}</div>
    @endif
    <table class="table table-bordered" id="tableSchedule">
        <tr class="text-center">
            <th>#</th>
            <th>Nama Bioskop</th>
            <th>Judul Film</th>
            <th>Harga Tiket</th>
            <th>Jam Tayang</th>
            <th>Aksi</th>
        </tr>
    </table>
    </div>
    <div class="modal fade" id="modalAdd" tabindex="-1" aria-labelledby="modalAddLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5" id="modalAddLabel">Tambah Data</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{route('staff.schedules.store')}}">
                @csrf
            <div class="modal-body">

                <div class="mb-3">
                  <label for="recipient-name" class="col-form-label">Bioskop:</label>
                  <select name="cinema_id" id="cinema_id" class="form-select @error('cinema_id') is-invalid @enderror">
                    <option disabled hidden selected>Pilih Bioskop</option>
                    {{--munculin data $cinemas--}}
                    @foreach ($cinemas as $cinema)
                        {{--opsi select muncul sesuai data--}}
                        {{--yang disimpan di db id  cinema yang munculin name--}}
                        <option value="{{$cinema['id']}}">{{$cinema['name']}}</option>
                    @endforeach
                  </select>
                  @error('cinema_id')
                      <small class="text-danger">{{$message}}</small>
                  @enderror
                </div>

                <div class="mb-3">
                  <label for="movie_id" class="col-form-label">Film:</label>
                  <select name="movie_id" id="movie_id" class="form-select @error('movie_id') is-invalid @enderror">
                    <option disabled hidden selected>Pilih Film</option>
                    @foreach ($movies as $movie)
                        <option value="{{$movie['id']}}">{{$movie['title']}}</option>
                    @endforeach
                  </select>
                  @error('movie_id')
                      <small class="text-danger">{{$message}}</small>
                  @enderror
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Harga:</label>
                    <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror">
                    @error('price')
                        <small class="text-danger">{{$message}}</small>
                    @enderror
                </div>

                <div class="mb-3">

                    <label for="hours" class="form-label">Jam Tayang:</label>
                    <input type="time" name="hours[]" id="hours" class="form-control @if($errors->has('hours.*')) is-invalid @endif">
                    @if ($errors->has('hours.*'))
                        <small class="text-danger">{{$errors->first('hours.*')}}</small>
                    @endif
                    {{--wadah untuk penambahan input dari js--}}
                    <div id="additionalInput"></div>
                    <span class="text-primary mt-3" style="cursor: pointer" onclick="addInput()">+ Tambah Input</span>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">Kirim</button>
            </div>
        </form>
          </div>
        </div>
      </div>
    </div>
@endsection

@push('script')
      <script>
        function addInput() {
            let content = `<input type="time" name="hours[]" class="form-control mt-3">`;
            //Panggil bagian yang akan diisi
            let wadah = document.querySelector('#additionalInput');
            //tambahkan konten, karna akan terus bertambah gunakan +-=
            wadah.innerHTML += content;
            }
      </script>
      {{--cek apakah ada error di form--}}
      @if ($errors->any())
      {{--jika ada err, munculkan modal melalui js--}}
      <script>
        let modalAdd = document.querySelector('#modalAdd');
        new bootstrap.Modal(modalAdd).show();
      </script>
      @endif

      <script>
         $(function() {
        $('#tableSchedule').DataTable({
            processing: true, //tanda load awal pas lg proses data
            serverSide: true, // data di proses di belakang (controller)
            ajax: "{{ route('staff.schedules.datatables') }}", // memanggil route
            columns: [ //urutan <td>
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'cinema', name: 'cinema', orderable: true, searchable: true },
                { data: 'movie', name: 'movie', orderable: true, searchable: true },
                { data: 'price', name: 'price', orderable: true, searchable: true},
                { data: 'hours', name: 'hours', orderable: true, searchable: true },
                { data: 'btnActions', name: 'btnActions', orderable: false, searchable: false },
            ]
        });
    })
      </script>
@endpush