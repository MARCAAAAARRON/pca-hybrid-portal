<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FieldDataReportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public array $filePaths;
    public array $fileNames;

    /**
     * Create a new message instance.
     */
    public function __construct(array $filePaths, array $fileNames)
    {
        $this->filePaths = $filePaths;
        $this->fileNames = $fileNames;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'PCA Field Data Report',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.field-data-report',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        
        foreach ($this->filePaths as $index => $path) {
            $name = $this->fileNames[$index] ?? basename($path);
            
            // Determine Mime Type
            $mime = 'application/octet-stream';
            if (str_ends_with(strtolower($name), '.pdf')) {
                $mime = 'application/pdf';
            } elseif (str_ends_with(strtolower($name), '.xlsx')) {
                $mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            }

            $attachments[] = \Illuminate\Mail\Mailables\Attachment::fromPath($path)
                ->as($name)
                ->withMime($mime);
        }

        return $attachments;
    }
}
