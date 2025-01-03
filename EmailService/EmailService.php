<?php

namespace App\Libraries\EmailService;

use CodeIgniter\Email\Email;

class EmailService
{
    protected $email;

    public function __construct()
    {
        $this->email = \Config\Services::email();
        $this->email->setFrom(setting('Email.fromEmail'), setting('Email.fromName'));
    }

    public function sendInvitationEmail($to, $user)
    {
        $subject = lang('Auth.magicLinkSubject');
        $message = view(setting('Auth.views')['invitation-to-access'], ['token' => $user->status_message]);

        return $this->sendEmail($to, $subject, $message);
    }

    public function sendCustomEmail($to, $subject, $message)
    {
        return $this->sendEmail($to, $subject, $message);
    }

    protected function sendEmail($to, $subject, $message)
    {
        $this->email->setTo($to);
        $this->email->setSubject($subject);
        $this->email->setMessage($message);

        return $this->email->send();
    }
}
