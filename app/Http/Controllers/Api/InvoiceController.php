<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = $request->user()
            ->invoices()
            ->with('client:id,name')
            ->withCount('items')
            ->latest()
            ->get();

        return response()->json($invoices);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0',
        ]);

        $invoice = DB::transaction(function () use ($request, $validated) {
            $total = 0;
            foreach ($validated['items'] as $item) {
                $total += $item['quantity'] * $item['rate'];
            }

            $number = $this->generateInvoiceNumber($request->user()->id);

            $invoice = $request->user()->invoices()->create([
                'client_id' => $validated['client_id'] ?? null,
                'number' => $number,
                'status' => 'draft',
                'total' => $total,
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'rate' => $item['rate'],
                    'amount' => $item['quantity'] * $item['rate'],
                ]);
            }

            return $invoice;
        });

        return response()->json($invoice->load('client:id,name', 'items'), 201);
    }

    public function show(Request $request, Invoice $invoice)
    {
        $this->authorizeOwnership($request, $invoice);

        return response()->json($invoice->load('client', 'items'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorizeOwnership($request, $invoice);

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'status' => 'in:draft,sent,paid,overdue',
            'issue_date' => 'sometimes|required|date',
            'due_date' => 'sometimes|required|date',
            'notes' => 'nullable|string',
        ]);

        $invoice->update($validated);

        return response()->json($invoice->load('client:id,name', 'items'));
    }

    public function destroy(Request $request, Invoice $invoice)
    {
        $this->authorizeOwnership($request, $invoice);
        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted.']);
    }

    public function markPaid(Request $request, Invoice $invoice)
    {
        $this->authorizeOwnership($request, $invoice);

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return response()->json($invoice);
    }

    public function downloadPdf(Request $request, Invoice $invoice)
    {
        $this->authorizeOwnership($request, $invoice);

        $invoice->load('client', 'items', 'user');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice-pdf', [
            'invoice' => $invoice,
        ]);

        return $pdf->download("{$invoice->number}.pdf");
    }

    protected function generateInvoiceNumber(int $userId): string
    {
        $count = Invoice::where('user_id', $userId)->count() + 1;
        return 'INV-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    protected function authorizeOwnership(Request $request, Invoice $invoice): void
    {
        if ($invoice->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized.');
        }
    }
}