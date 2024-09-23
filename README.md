# Nette Microsoft Mailer

This library provides e-mail sending via Microsoft Graph API.

---

[![main workflow](https://github.com/adbrosaci/nette-microsoft-mailer/actions/workflows/main.yml/badge.svg)](https://github.com/adbrosaci/nette-microsoft-mailer/actions/workflows/main.yml)
[![Licence](https://img.shields.io/packagist/l/adbros/nette-microsoft-mailer.svg?style=flat-square)](https://packagist.org/packages/adbros/nette-microsoft-mailer)
[![Downloads this Month](https://img.shields.io/packagist/dm/adbros/nette-microsoft-mailer.svg?style=flat-square)](https://packagist.org/packages/adbros/nette-microsoft-mailer)
[![Downloads total](https://img.shields.io/packagist/dt/adbros/nette-microsoft-mailer.svg?style=flat-square)](https://packagist.org/packages/adbros/nette-microsoft-mailer)
[![Latest stable](https://img.shields.io/packagist/v/adbros/nette-microsoft-mailer.svg?style=flat-square)](https://packagist.org/packages/adbros/nette-microsoft-mailer)

## How to install

```bash
composer require adbros/nette-microsoft-mailer
```

## Register mailer

Just rewrite the default mailer service in your `neon` file.

```neon
services:
	mail.mailer: Adbros\MicrosoftMailer\MicrosoftMailer(
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
