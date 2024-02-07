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
    public $orgId;

    /**
     * Create a new message instance.
     *
     * @param  string  $setPasswordLink
     * @return void
     */
    public function __construct($setPasswordLink, $name, $org, $orgId)
    {
        $this->setPasswordLink = $setPasswordLink;
        $this->name = $name;
        $this->org = $org;
        $this->orgId = $orgId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = trans('usermgmt')['mails']['account_created_subject'];

        $markdownView = 'usermgmt::mails.set_password';

        if($this->orgId == config('app.up_organization_id')){
            app()->setLocale('de');
            $markdownView = 'usermgmt::mails.uplandcare.set_password';
        }

        return $this->markdown($markdownView)->subject($subject);                    
    }
}
