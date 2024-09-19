# Nette Microsoft Mailer

This library provides e-mail sending via Microsoft Graph API.

## How to install

```bash
composer require adbros/nette-microsoft-mailer
```

## Register mailer

Just rewrite the default mailer service in your `neon` file.

```neon
services:
	mail.mailer: Adbros\NetteMicrosoftMailer\MicrosoftMailer(
		tenantId: 'tenant_id'
		clientId: 'client_id'
		clientSecret: 'client_secret'
		defaultSender: 'default_sender_email'
	)
```
## Usage

Use as standard Nette Mailer.

```php
<?php

use Nette\Mail\Mailer;
use Nette\Mail\Message;

class SomeClass
{

    public function __construct(
        private Mailer $mailer,
    ) 
    {    
    }
    
    public function sendEmail(): void
    {
        $message = new Message();
        $message->setSubject('Hello World!');
        $message->setHtmlBody('<h1>Hello World!</h1>');
        $message->addTo('john.doe@example.org');
        
        $this->mailer->send($message);
    }
    
}
```
