<?php
namespace App\Observers;

use App\Models\Invoice;
use App\Models\Activity;

class InvoiceObserver
{
    public function created(Invoice $invoice)
    {
        $this->log($invoice, 'created', "Invoice {$invoice->number} was created");
    }

    public function updated(Invoice $invoice)
    {
        if ($invoice->wasChanged('status')) {
            if ($invoice->status === 'paid') {
                $this->log($invoice, 'paid', "Invoice {$invoice->number} was paid");
            } elseif ($invoice->status === 'sent') {
                $this->log($invoice, 'sent', "Invoice {$invoice->number} was sent to client");
            }
        }
    }

    private function log($invoice, $action, $description)
    {
        if ($invoice->user_id) {
            Activity::create([
                'user_id' => $invoice->user_id,
                'type' => 'invoice',
                'action' => $action,
                'description' => $description,
            ]);
        }
    }
}