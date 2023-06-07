<?php

namespace App\Http\Controllers\Apps;

use Inertia\Inertia;
use App\Models\Profit;
use Barryvdh\DomPDF\Facade\PDF;
use Illuminate\Http\Request;
use App\Exports\ProfitExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ProfitController extends Controller
{
    public function index()
    {
        return Inertia::render("Apps/Profits/Index");
    }

    public function filter(Request $request)
    {
        $this->validate($request, [
            "start_date" => ["required"],
            "end_date" => ["required"]
        ]);

        $profits = Profit::with("transaction")->whereDate("created_at", ">=", $request->start_date)->whereDate("created_at", "<=", $request->end_date)->get();

        $total = Profit::whereDate("created_at", ">=", $request->start_date)->whereDate("created_at", "<=", $request->end_date)->sum("total");

        return Inertia::render("Apps/Profits/Index", [
            "profits" => $profits,
            "total" => $total
        ]);
    }

    public function export(Request $request)
    {
        return Excel::download(new ProfitExport($request->start_date, $request->end_date), 'profits : ' . $request->start_date . ' — ' . $request->end_date . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    public function pdf(Request $request)
    {
        $profits = Profit::with('transaction')->whereDate('created_at', '>=', $request->start_date)->whereDate('created_at', '<=', $request->end_date)->get();

        $total = Profit::whereDate('created_at', '>=', $request->start_date)->whereDate('created_at', '<=', $request->end_date)->sum('total');

        $pdf = PDF::loadView('exports.profits', [
            "profits" => $profits,
            "total" => $total
        ]);

        return $pdf->download('profits : ' . $request->start_date . ' — ' . $request->end_date . '.pdf');
    }
}
