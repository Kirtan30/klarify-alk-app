<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SelfTestResultsExportMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $filePaths, $heading;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($filePaths)
    {
        $this->filePaths = $filePaths;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.self_test_results', ['files' => $this->filePaths]);
    }
}
