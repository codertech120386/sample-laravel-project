<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\User;
use App\Invoice;

class RequestPDFInvoice extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $invoice;

    public function __construct(User $user, Invoice $invoice)
    {
        $this->user = $user;
        $this->invoice = $invoice;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('support@coffic.com')
            ->replyTo('support@coffic.com')
            ->view('invoice.requestPDF');
    }
}
