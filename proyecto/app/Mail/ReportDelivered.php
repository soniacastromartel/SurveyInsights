<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportDelivered extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($emailData)
    {
        //
        $this -> email= $emailData;
        $this-> path = env('PUBLIC_PATH');

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->email['from'])
        ->subject($this->email['subject'])
        ->view($this->email['view'])
        ->attachData ($this->email['file'], 'informe.pdf',
        // ->attach ($this->path,
        [
            'as' => 'informe.pdf', 
            'mime' => 'application/pdf',
    ])
        ->with([
         'emailData' => $this->email
    ]);
    }
}
