<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>TIXID</title>
    <!--jcquery prioritas-->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet" />
    <!-- MDB -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/9.1.0/mdb.min.css" rel="stylesheet" />
    <!-- datatables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.min.css">
</head>
<body>
        <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
    <!-- Container wrapper -->
    <div class="container">
        <!-- Navbar brand -->
        <a class="navbar-brand me-2" href="https://mdbgo.com/">
        <img
            src="data:image/jpeg;base64,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"
            height="25"
            alt="MDB Logo"
            loading="lazy"
            style="margin-top: -1px;"
        />
        </a>

        <!-- Toggle button -->
        <button
        data-mdb-collapse-init
        class="navbar-toggler"
        type="button"
        data-mdb-target="#navbarButtonsExample"
        aria-controls="navbarButtonsExample"
        aria-expanded="false"
        aria-label="Toggle navigation"
        >
        <i class="fas fa-bars"></i>
        </button>

        <!-- Collapsible wrapper -->
        <div class="collapse navbar-collapse" id="navbarButtonsExample">
        <!-- Left links -->
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            @if (Auth::check()&& Auth::user()->role == 'admin')
                <li class="nav-item">
                    <a class="nav-link" href="#">Dashboard</a>
                </li>
                <li class="nav-item dropdown">
                    <a
                      data-mdb-dropdown-init
                      class="nav-link dropdown-toggle"
                      href="#"
                      id="navbarDropdownMenuLink"
                      role="button"
                      aria-expanded="false"
                    >
                      Data Master
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                      <li>
                        <a class="dropdown-item" href="{{route('admin.cinemas.index')}}">Data Bioskop</a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{route('admin.movies.index')}}">Data Film</a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{route('admin.users.index')}}">Data Petugas</a>
                      </li>
                    </ul>
                  </li>
                  @elseif(Auth::check()&& Auth::user()->role == 'staff')
                  <li class="nav-item">
                    <a class="nav-link" href="{{route('staff.schedules.index')}}">Jadwal Tiket</a>
                 </li>
                 <li class="nav-item">
                    <a class="nav-link" href="{{route('staff.promos.index')}}">Promo</a>
                 </li>

            @else
            <li class="nav-item">
                <a class="nav-link" href="{{route('home')}}">Beranda</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('cinemas.list') }}">Bioskop</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('tickets.index') }}">Tiket</a>
            </li>
            @endif
        </ul>
        <!-- Left links -->

        <div class="d-flex align-items-center">
            @if (Auth::check())
            {{--Auth::check() : ngecek uda login atau belum--}}
                <a href="{{route('logout')}}" class="btn btn-danger">Logout</a>
            @else
                <a href="{{route('login')}}" class="btn btn-link px-3 me-2" type="button">Login</a>
                <a href="{{route('signup')}}" class="btn btn-primary me-3" type="button">Sign Up</a>
            @endif
        </div>
        </div>
        <!-- Collapsible wrapper -->
    </div>
    <!-- Container wrapper -->
    </nav>
    <!-- Navbar -->

    {{--konten dinamis HTML--}}
    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
        integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous">
    </script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/9.1.0/mdb.umd.min.js"></script>
    {{-- cdn chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
    @stack('script')


</body>
</html>