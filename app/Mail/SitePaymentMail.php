<?php

namespace App\Mail;

use App\Models\SitePayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class SitePaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    public SitePayment $payment;
    protected string $pdfContent;

    public function __construct(SitePayment $payment, string $pdfContent)
    {
        $this->payment = $payment;
        $this->pdfContent = $pdfContent;
    }

    public function build()
    {
        $date = Carbon::parse($this->payment->date)->format('d-m-Y');

        return $this->subject('Payment received on date (' . $date . ')')
            ->view('emails.site_payment')
            ->attachData($this->pdfContent, 'site_payment_' . $this->payment->id . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
