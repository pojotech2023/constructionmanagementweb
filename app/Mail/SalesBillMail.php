<?php

namespace App\Mail;

use App\Models\SalesBill;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SalesBillMail extends Mailable
{
    use Queueable, SerializesModels;

    public SalesBill $salesBill;
    protected string $pdfContent;

    public function __construct(SalesBill $salesBill, string $pdfContent)
    {
        $this->salesBill = $salesBill;
        $this->pdfContent = $pdfContent;
    }

    public function build()
    {
        return $this->subject(config('app.name') . ' Sales Bill')
            ->view('emails.sales_bill')
            ->attachData($this->pdfContent, 'sales_bill_' . $this->salesBill->id . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
