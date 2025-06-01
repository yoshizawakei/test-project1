<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $message_body;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $message_body)
    {
        $this->name = $name;
        $this->message_body = $message_body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.test')
                    ->subject('Test Email from Laravel')
                    ->with([
                        'name' => $this->name,
                        'message_body' => $this->message_body,
                    ]);
    }
}
