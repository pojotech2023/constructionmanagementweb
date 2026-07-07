<?php

namespace App\Mail;

use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Quotation $quotation;
    protected string $pdfContent;

    public function __construct(Quotation $quotation, string $pdfContent)
    {
        $this->quotation = $quotation;
        $this->pdfContent = $pdfContent;
    }

    public function build()
    {
        return $this->subject(config('app.name') . ' Quotation')
            ->view('emails.quotation')
            ->attachData($this->pdfContent, 'quotation_' . $this->quotation->id . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
