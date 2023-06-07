<?php

namespace App\Http\Controllers\Apps;

use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Transaction;
use App\Exports\SalesExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware(["permission:sales.index"]);
    }

    public function index()
    {
        return Inertia::render("Apps/Sales/Index");
    }

    public function filter(Request $request)
    {
        $this->validate($request, [
            "start_date" => ["required"],
            "end_date" => ["required"]
        ]);

        $sales = Transaction::with("customer", "cashier")->whereDate("created_at", ">=", $request->start_date)->whereDate("created_at", "<=", $request->end_date)->get();

        $total = Transaction::whereDate("created_at", ">=", $request->start_date)->whereDate("created_at", "<=", $request->end_date)->sum("grand_total");

        return Inertia::render("Apps/Sales/Index", [
            "sales" => $sales,
            "total" => $total
        ]);
    }

    public function export(Request $request)
    {
        return Excel::download(new SalesExport($request->start_date, $request->end_date), 'sales : ' . $request->start_date . ' — ' . $request->end_date . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    public function pdf(Request $request)
    {
        $sales = Transaction::with('cashier', 'customer')->whereDate('created_at', '>=', $request->start_date)->whereDate('created_at', '<=', $request->end_date)->get();

        $total = Transaction::whereDate('created_at', '>=', $request->start_date)->whereDate('created_at', '<=', $request->end_date)->sum('grand_total');

        $pdf = PDF::loadView('exports.sales', [
            "sales" => $sales,
            "total" => $total
        ]);

        return $pdf->download('sales : ' . $request->start_date . ' — ' . $request->end_date . '.pdf');
    }
}