<?php

namespace App\Http\Controllers;

use App\Models\promo;
use App\Models\schedule;
use App\Models\ticket;
use Illuminate\Http\Request;
use App\Models\TicketPayment;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;




class TicketController extends Controller
{

    public function showSeats($scheduleId, $hourId)
    {
        $schedule = schedule::find($scheduleId);
        // ambil jam yang index nya sesuai param route
        $hour = $schedule['hours'][$hourId] ?? ''; // kalau tidak ketemu jam nya, buat default kosong

        $soldSeats = Ticket::where('schedule_id', $scheduleId)->where('actived', 1)->where('date', now()->format('Y-m-d'))->pluck('rows_of_seats');
        // pluck ambil datanya hanya dari 1 kolom
        $soldSeatsFormat = [];
        foreach ($soldSeats as $key => $seat) {
            // karna soldSeats bentuknya array 2d jadi loop 2 kali simpan ke array diatas untuk data 1d
            foreach ($seat as $item) {
                array_push($soldSeatsFormat, $item);
            }
        }

        // dd($soldSeatsFormat);
        return view ('schedule.row-seats', compact('schedule', 'hour', 'soldSeatsFormat'));
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = Auth::user()->id;
        // ambil data tiket berdasarkan pengguna
        $ticketActive = Ticket::where('user_id', $userId)->where('actived', 1)->where('date', now()->format('Y-m-d'))->get();
        // ambil data ticket berdasarkan data siapa yang login, yg non-aktif dan sudah kadaluarsa
        $ticketNonActive = Ticket::where('user_id', $userId)->orWhere('date', '<>', now()->format('Y-m-d'))->get();
        // <> bukan sama dengan
        return view('ticket.index', compact('ticketActive', 'ticketNonActive'));
    }

    public function dataChart()
    {
        // ambil bulan saat ini
        $month = now()->format('m');
        // hasil collection (get), dikelompokan berdasarkan booked_date
        // toArray() : ubah collection menjadi array untuk memudahkan pengambilan data
        $tickets = Ticket::where('actived', 1)->whereHas('ticketPayment', function($q) use ($month) {
            $q->whereMonth('booked_date', $month);
        })->get()->groupBy(function ($ticket) {
            return Carbon::parse($ticket->ticketPayment->booked_date)->format ('Y-m-d');
        })->toArray();
        // dd($tickets);

        // ambil key dari array assoc (tanggal)
        $labels = array_keys($tickets);
        // siapkan wadah untuk array yg akan berisi angka" jumlah data di tgl tersebut
        $data = [];
        foreach ($tickets as $ticketGroup) {
            array_push($data, count($ticketGroup));
        }
        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'schedule_id' => 'required',
            'date' => 'required',
            'hour' => 'required',
            'rows_of_seats' => 'required',
            'quantity' => 'required',
            'total_price' => 'required',
            'service_fee' => 'required',
        ]);

        $createData = Ticket::create([
            'user_id' => $request->user_id,
            'schedule_id' => $request->schedule_id,
            'date' => $request->date,
            'hour' => $request->hour,
            'rows_of_seats' => $request->rows_of_seats,
            'quantity' => $request->quantity,
            'total_price' => $request->total_price,
            'service_fee' => $request->service_fee,
            'actived' => 0, //blm aktif sebelum dibayar
        ]);

        // karena ini diproses di JS jd returnya juga bentuk response json
        return response()->json([
            'message' => 'berhasil membuat data tiket',
            'data' => $createData
        ]);
    }

    public function ticketOrderPage($ticketId)
    {
        $ticket = ticket::where('id', $ticketId)->with('schedule', 'schedule.cinema', 'schedule.movie')->first();
        $promos = promo::where('actived', 1)->get();
        return view('schedule.order', compact('ticket', 'promos'));
    }

    public function createBarcode(Request $request)
    {
        $kodeBarcode = 'TICKET' . $request->ticket_id;
        $qrImage = QrCode::format('svg')
        ->size(300) // ukuran pixel
        ->margin(2) // margin tepi
        ->errorCorrection('H') // tingkat koreksi error: L, M, Q, H
        ->generate($kodeBarcode);

        $fileName = $kodeBarcode . '.svg';
        $path = 'barcode/' . $fileName;

        Storage::disk('public')->put($path, $qrImage);
        $createData = TicketPayment::create([
            'ticket_id' => $request->ticket_id,
            'qrcode' => $path,
            'status' => 'process',
            'booked_date' => now(),
        ]);


        $ticket = Ticket::find($request->ticket_id);
        $totalPrice = $ticket->total_price;

        if ($request->promo_id != NULL) {
            $promo = Promo::find($request->promo_id);
            if ($promo['type'] == 'percent') {
                $discount = $ticket['total_price'] * $promo['discount'] / 100;
            } else {
                $discount = $promo['discount'];
            }
            $totalPrice = $ticket['total_price'] - $discount;
        }
        $updateTicket = Ticket::where('id', $request->ticket_id)->update([
            'promo_id' => $request->promo_id,
            'total_price' => $totalPrice,
        ]);

        return response()->json([
            'message' => 'Berhasil membuat pesanan tiket sementara!',
            'data' => $createData
        ]);
    }

    public function ticketPaymentPage($ticketId)
    {
        $ticket = Ticket::where('id', $ticketId)->with('schedule', 'promo', 'schedule.cinema', 'schedule.movie')->first();
        return view('schedule.payment', compact('ticket'));
    }

    public function updateStatusTicket($ticketId)
    {
        $updatePayment = TicketPayment::where('ticket_id', $ticketId)->update(['paid_date' => now()]);
        $updateStatus = Ticket::where('id', $ticketId)->update(['actived' => 1]);
        // diarahkan ke halaman route(web.php) tickets.show untuk memunculkan tiket
        return redirect()->route('tickets.show', $ticketId);
    }


    public function show($ticketId)
    {
        $ticket = Ticket::where('id', $ticketId)->with('schedule', 'schedule.movie', 'schedule.cinema')->first();
        return view('schedule.ticket', compact('ticket'));
    }

    public function exportPdf($ticketId)
    {
        // siapkan data yang akan ditampilkan di pdf, hasilnya harus bentuk array (toArray())
        $ticket = Ticket::where('id', $ticketId)->with(['schedule', 'schedule.movie', 'schedule.cinema', 'ticketPayment'])->first()->toArray();
        // buat nama var yg digunakan di blade pdf
       view()->share('ticket', $ticket);
        // menentukan file blade yang dicetak dan dikirim juga datanya
        $pdf = Pdf::loadView('schedule.export-pdf', $ticket);
        $fileName = 'TICKET' . $ticketId . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ticket $ticket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ticket $ticket)
    {
        //
    }
}
