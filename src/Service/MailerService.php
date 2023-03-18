<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    public function __construct(private readonly MailerInterface $mailer)
    {
    }

    public function sendWelcomeEmail(User $user): void
    {
        $email = (new Email())
            ->from($_ENV['FROM_EMAIL'])
            ->to($user->getEmail())
            ->subject('Welcome to ToDoList!')
            ->text('Nice to meet you ' . $user->getUsername() . "! ❤️");

        $this->mailer->send($email);
    }

    public function sendChangingPasswordEmail(User $user): void
    {
        $email = (new Email())
            ->from($_ENV['FROM_EMAIL'])
            ->to($user->getEmail())
            ->subject('Password change in ToDoList!')
            ->text('Your password is changed.  ' . $user->getUsername() . "!");

        $this->mailer->send($email);
    }
}
