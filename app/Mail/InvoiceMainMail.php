<?php

namespace App\Mail;

use App\Models\InvoiceMain;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMainMail extends Mailable
{
    use Queueable, SerializesModels;

    public InvoiceMain $invoiceMain;
    public string $type;

    /**
     * Create a new message instance.
     */
    public function __construct(InvoiceMain $invoiceMain, string $type)
    {
        $this->invoiceMain = $invoiceMain;
        $this->type = $type;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-main-mail',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath(public_path($this->invoiceMain->pdf_path))
                ->as($this->invoiceMain->invoice_number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
