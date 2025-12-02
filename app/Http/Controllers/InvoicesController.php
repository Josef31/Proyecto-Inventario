<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;

class InvoicesController extends Controller
{
    public function index()
    {
        // Obtener facturas de ventas
        $salesInvoices = Sale::with(['user', 'customer'])
            ->where('status', 'completada')
            ->whereNotNull('invoice_number')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($sale) {
                return [
                    'id' => $sale->invoice_number,
                    'type' => 'venta',
                    'invoice_number' => $sale->invoice_number,
                    'date' => $sale->created_at,
                    'entity' => $sale->customer ? $sale->customer->name : 'Cliente General',
                    'total' => $sale->total,
                    'user' => $sale->user->name,
                    'payment_method' => $sale->payment_method_id,
                ];
            });

        // Obtener facturas de compras
        $purchaseInvoices = Purchase::with(['supplier', 'user'])
            ->whereNotNull('invoice_number')
            ->orderBy('purchase_date', 'desc')
            ->get()
            ->map(function($purchase) {
                return [
                    'id' => $purchase->invoice_number,
                    'type' => 'compra',
                    'invoice_number' => $purchase->invoice_number,
                    'date' => $purchase->purchase_date,
                    'entity' => $purchase->supplier->name,
                    'total' => $purchase->total_amount,
                    'user' => $purchase->user->name,
                    'payment_method' => null,
                ];
            });

        // Combinar y ordenar por fecha
        $invoices = $salesInvoices->concat($purchaseInvoices)
            ->sortByDesc('date')
            ->values();

        $totalInvoices = $invoices->count();

        return view('invoices.index', compact('invoices', 'totalInvoices'));
    }

    public function show($invoiceNumber)
    {
        // Buscar en ventas
        $sale = Sale::with(['user', 'customer', 'items.product'])
            ->where('invoice_number', $invoiceNumber)
            ->first();

        if ($sale) {
            return view('invoices.show-sale', compact('sale'));
        }

        // Buscar en compras
        $purchase = Purchase::with(['supplier', 'user', 'items.product'])
            ->where('invoice_number', $invoiceNumber)
            ->first();

        if ($purchase) {
            return view('invoices.show-purchase', compact('purchase'));
        }

        abort(404, 'Factura no encontrada');
    }

    public function getInvoices()
    {
        return $this->index();
    }

    public function getInvoiceDetails($invoiceNumber)
    {
        // Buscar en ventas
        $sale = Sale::with(['user', 'customer', 'items.product'])
            ->where('invoice_number', $invoiceNumber)
            ->first();

        if ($sale) {
            return response()->json([
                'type' => 'venta',
                'data' => $sale
            ]);
        }

        // Buscar en compras
        $purchase = Purchase::with(['supplier', 'user', 'items.product'])
            ->where('invoice_number', $invoiceNumber)
            ->first();

        if ($purchase) {
            return response()->json([
                'type' => 'compra',
                'data' => $purchase
            ]);
        }

        return response()->json(['error' => 'Factura no encontrada'], 404);
    }
}