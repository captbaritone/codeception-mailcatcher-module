<?php
$I = new NoGuy($scenario);
$I->wantTo('Run a Mailcatcher cept test');

// Cleared old emails from MailCatcher
$I->resetEmails();

// Send an email
$body = "Testing";
mail('user@example.com', 'Subject Line', $body);

$I->seeInLastEmail($body);

