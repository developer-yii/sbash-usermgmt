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
    public $name;
    public $org;

    /**
     * Create a new message instance.
     *
     * @param  string  $setPasswordLink
     * @return void
     */
    public function __construct($setPasswordLink, $name, $org)
    {
        $this->setPasswordLink = $setPasswordLink;
        $this->name = $name;
        $this->org = $org;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = trans('usermgmt')['mails']['account_created_subject'];

        return $this->markdown('usermgmt::mails.set_password')->subject($subject);                    
    }
}
