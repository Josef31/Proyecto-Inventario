<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicesController extends Controller
{
    public function index()
    {
        $invoices = Sale::with(['user', 'items'])
            ->completed()
            ->orderBy('created_at', 'desc')
            ->get();
        
        $totalInvoices = $invoices->count();

        return view('invoices.index', compact('invoices', 'totalInvoices'));
    }

    public function show($id)
    {
        $invoice = Sale::with(['user', 'items.product'])
            ->completed()
            ->findOrFail($id);

        return view('invoices.show', compact('invoice'));
    }

    public function print($id)
    {
        $invoice = Sale::with(['user', 'items.product'])
            ->completed()
            ->findOrFail($id);

        // Marcar como impresa
        $invoice->markAsPrinted();

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        
        return $pdf->download('factura-' . $invoice->invoice_number . '.pdf');
    }

    public function getInvoices()
    {
        $invoices = Sale::with(['user', 'items'])
            ->completed()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($invoices);
    }

    public function getInvoiceDetails($id)
    {
        $invoice = Sale::with(['user', 'items.product'])
            ->completed()
            ->findOrFail($id);

        return response()->json($invoice);
    }
}