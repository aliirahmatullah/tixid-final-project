<?php

namespace App\Http\Controllers;

use App\Models\promo;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PromoExport;
use Yajra\DataTables\Facades\DataTables;


class PromoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $promos = promo::all();
        return view('staff.promo.index', compact('promos'));
    }

    public function datatables()
    {
        $promos = promo::query()->get();
        return DataTables::of($promos)->addIndexColumn()
        ->addColumn('discount', function($promo) {
            if($promo->type == 'percent') {
            return $promo->discount . '%';
            } else {
            return 'Rp.' .  number_format($promo->discount, 0, ',', '.') ;
            }
        })
        ->addColumn('btnActions', function($promo) {
            $btnEdit = '<a href="'. route('staff.promos.edit', $promo['id']) .'" class="btn btn-secondary me-2">Edit</a>';
            $btnDelete = '<form action="'. route('staff.promos.delete', $promo['id']) .'" method="POST"> '.
            @csrf_field() .
            @method_field('DELETE') . '
            <button class="btn btn-danger">Hapus</button>
        </form>';
        return '<div class="d-flex">' . $btnEdit . $btnDelete . '</div>';

        })
        ->rawColumns(['btnActions'])
        ->make(true);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('staff.promo.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'promo_code' => 'required',
            'type' => 'required|in:percent,rupiah',
            'discount' => 'required',
        ], [
            'promo_code.required' => 'Kode Promo wajib diisi',
            'type.required' => 'Tipe Promo wajib diisi',
        ]);

            // validasi discount tergantung type
        if ($request->type === 'percent') {
            $request->validate([
                'discount' => 'required|numeric|min:1|max:100',
            ], [
                'discount.required' => 'Jumlah Potongan wajib diisi',
                'discount.min' => 'Jumlah Potongan minimal 1%',
                'discount.max' => ' Jumlah Potongan maksimal 100%',
            ]);
        } elseif ($request->type === 'rupiah') {
            $request->validate([
                'discount' => 'required|numeric|min:1000',
            ], [
                'discount.required' => 'Jumlah Potongan wajib diisi',
                'discount.min' => 'Jumlah Potongan minimal Rp 1000',
            ]);
        }


        $createData = promo::create([
            'promo_code' => $request->promo_code,
            'type' => $request->type,
            'discount' => $request->discount,
            'actived' => 1
        ]);

        if ($createData) {
            return redirect()->route('staff.promos.index')->with('success', 'Berhasil tambah data promo!');
        } else {
            return redirect()->back()->with('error', 'Gagal! Silahkan coba lagi');
        }

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $promos = Promo::find($id);
        return view('staff.promo.edit', compact('promos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'promo_code' => 'required',
            'type' => 'required|in:percent,rupiah',
            'discount' => 'required',
        ], [
            'promo_code.required' => 'Kode Promo wajib diisi',
            'type.required' => 'Tipe Promo wajib diisi',
        ]);

        // validasi discount tergantung type
        if ($request->type === 'percent') {
            $request->validate([
                'discount' => 'required|numeric|min:1|max:100',
            ], [
                'discount.required' => 'Jumlah Potongan wajib diisi',
                'discount.min' => 'Jumlah Potongan minimal 1%',
                'discount.max' => ' Jumlah Potongan maksimal 100%',
            ]);
        } elseif ($request->type === 'rupiah') {
            $request->validate([
                'discount' => 'required|numeric|min:1000',
            ], [
                'discount.required' => 'Jumlah Potongan wajib diisi',
                'discount.min' => 'Jumlah Potongan minimal Rp 1000',
            ]);
        }

        $updateData = promo::where('id', $id)->update([
            'promo_code' => $request->promo_code,
            'type' => $request->type,
            'discount' => $request->discount,
            'actived' => 1
        ]);

        if ($updateData) {
            return redirect()->route('staff.promos.index')->with('success', 'Berhasil edit data promo!');
        } else {
            return redirect()->back()->with('error', 'Gagal! Silahkan coba lagi');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        promo::where('id', $id)->Delete();
        return redirect()->route('staff.promos.index')->with('success', 'Berhasil menghapus data!');
    }

    public function trash()
    {
       $promos = promo::onlyTrashed()->get();
        return view('staff.promo.trash', compact('promos'));
    }

    public function restore($id)
    {
       $promos = promo::onlyTrashed()->find($id);
       $promos->restore();
        return redirect()->route('staff.promos.index')->with('success', 'Berhasil mengembalikan data!');
    }

    public function deletePermanent($id)
    {
       $promos = promo::onlyTrashed()->find($id);
       $promos->forceDelete();
        return redirect()->route('staff.promos.index')->with('success', 'Berhasil menghapus data secara permanen!');
    }

    public function exportExcel()
    {
        //nama file yang akan diunduh
        $fileName = 'data-promo.xlsx';
        //proses download
        return Excel::download(new PromoExport, $fileName);
    }
}
