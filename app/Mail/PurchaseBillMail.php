<?php

namespace App\Mail;

use App\Models\PurchaseBill;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchaseBillMail extends Mailable
{
    use Queueable, SerializesModels;

    public PurchaseBill $purchaseBill;
    protected string $pdfContent;

    public function __construct(PurchaseBill $purchaseBill, string $pdfContent)
    {
        $this->purchaseBill = $purchaseBill;
        $this->pdfContent = $pdfContent;
    }

    public function build()
    {
        return $this->subject(config('app.name') . ' Purchase Bill')
            ->view('emails.purchase_bill')
            ->attachData($this->pdfContent, 'purchase_bill_' . $this->purchaseBill->id . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
