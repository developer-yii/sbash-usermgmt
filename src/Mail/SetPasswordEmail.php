<?php
namespace Sbash\Usermgmt\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SetPasswordEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $setPasswordLink;

    /**
     * Create a new message instance.
     *
     * @param  string  $setPasswordLink
     * @return void
     */
    public function __construct($setPasswordLink)
    {
        $this->setPasswordLink = $setPasswordLink;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('usermgmt::mails.set_password')
                    ->with(['setPasswordLink' => $this->setPasswordLink]);
    }
}
