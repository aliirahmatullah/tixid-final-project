<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>TIXID</title>

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet">

  <!-- MDB -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/9.1.0/mdb.min.css" rel="stylesheet">
</head>
<body>
    {{--Mengecek jika ada with('success') dari controllernya, jika ada munculkan didalam alert success --}}
    @if (Session::get('success'))
        <div class="alert alert-success">{{ Session::get('success')}}</div>
    @endif

    @if (Session::get('error'))
        <div class="alert alert-danger">{{ Session::get('error')}}</div>
    @endif
  <form class="w-50 d-block m-auto my-5" method="POST" action="{{route('login.auth')}}">
    @csrf
    <!-- Email input -->
    @error('email')
        <small class="text-danger">{{ $message }}</small>
    @enderror
    <div data-mdb-input-init class="form-outline mb-4">
      <input type="email" id="formExample1" class="form-control @error('email') is-invalid @enderror" name="email">
      <label for="formExample1" class="form-label">Email</label>
    </div>

    <!-- Password input -->
    @error('password')
        <small class="text-danger">{{ $message }}</small>
    @enderror
    <div data-mdb-input-init class="form-outline mb-4">
      <input type="password" id="formExample2" class="form-control @error('password') is-invalid @enderror" name="password">
      <label for="formExample2" class="form-label">Password</label>
    </div>

    <!-- Submit button -->
    <button data-mdb-input-init type="submit" class="btn btn-primary btn-block">Login</button>

    <div class="text-center mt-3">
      <a href="{{ route('home') }}">Kembali</a>
    </div>
  </form>

  <!-- Bootstrap & Popper -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>

  <!-- MDB -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/9.1.0/mdb.umd.min.js"></script>
</body>
</html>
