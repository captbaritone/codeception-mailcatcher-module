<?php

class MailcatcherCest
{
    public function _before(\NoGuy $I) {
        // Clear old emails from MailCatcher
        $I->resetEmails();
    }

    public function test_reset_emails(\NoGuy $I)
    {
        $body = "Hello World!";
        mail('user@example.com', 'Subject Line', $body);
        $I->seeEmailCount(1);
        $I->resetEmails();
        $I->seeEmailCount(0);
    }

    public function test_see_in_last_email(\NoGuy $I)
    {
        $body = "Hello World!";
        mail('user@example.com', 'Subject Line', $body);
        $I->seeInLastEmail($body);
    }

    public function test_see_in_last_email_subject(\NoGuy $I)
    {
        $subject = 'Subject Line';
        mail('user@example.com', $subject, "Hello World!");
        $I->seeInLastEmailSubject($subject);
    }

    public function test_dont_see_in_last_email_subject(\NoGuy $I)
    {
        $subject = 'Subject Line';
        mail('user@example.com', $subject, "Hello World!");
        sleep(1); // Prevents a race condition
        mail('user@example.com', 'Another Subject', "Hello World!");
        $I->dontSeeInLastEmailSubject($subject);
    }

    public function test_dont_see_in_last_email(\NoGuy $I)
    {
        $body = "Hello World!";
        mail('user@example.com', 'Subject Line', $body);
        sleep(1); // Prevents a race condition
        mail('user@example.com', 'Subject Line', "Goodbye World!");
        $I->dontSeeInLastEmail($body);
    }

    public function test_see_in_last_email_to(\NoGuy $I)
    {
        $body = "Hello World!";
        $user = "userA@example.com";
        mail($user, 'Subject Line', $body);
        mail('userB@example.com', 'Subject Line', "Goodbye Word!");
        $I->seeInLastEmailTo($user, $body);
    }

    public function test_dont_see_in_last_email_to(\NoGuy $I)
    {
        $body = "Goodbye Word!";
        $user = "userA@example.com";
        mail($user, 'Subject Line',  "Hello World!");
        mail('userB@example.com', 'Subject Line', $body);
        $I->dontSeeInLastEmailTo($user, $body);
    }

    public function test_see_in_last_email_subject_to(\NoGuy $I)
    {
        $subject = 'Subject Line';
        $user = "userA@example.com";
        mail($user, $subject, "Hello World!");
        mail('userB@example.com', 'Subject Line', "Goodbye Word!");
        $I->seeInLastEmailSubjectTo($user, $subject);
    }

    public function test_dont_see_in_last_email_subject_to(\NoGuy $I)
    {
        $subject = "Subject Line";
        $user = "userA@example.com";
        mail($user, 'Nothing to see here', "Hello World!");
        mail('userB@example.com', $subject, "Hello World!");
        $I->dontSeeInLastEmailSubjectTo($user, $subject);
    }

    public function test_grab_matches_from_last_email(\NoGuy $I)
    {
        mail("user@example.com", 'Subject Line',  "Hello World!");
        $matches = $I->grabMatchesFromLastEmail("/Hello (World)/");
        $I->assertEquals($matches, array('Hello World', 'World'));
    }

    public function test_grab_from_last_email(\NoGuy $I)
    {
        mail("user@example.com", 'Subject Line',  "Hello World!");
        $match = $I->grabFromLastEmail("/Hello (World)/");
        $I->assertEquals($match, "Hello World");
    }

    public function test_grab_matches_from_last_email_to(\NoGuy $I)
    {
        $user = "user@example.com";
        mail($user, 'Subject Line',  "Hello World!");
        mail("userB@example.com", 'Subject Line',  "Nothing to see here");
        $matches = $I->grabMatchesFromLastEmailTo($user, "/Hello (World)/");
        $I->assertEquals($matches, array('Hello World', 'World'));
    }

    public function test_grab_from_last_email_to(\NoGuy $I)
    {
        $user = "user@example.com";
        mail($user, 'Subject Line',  "Hello World!");
        mail("userB@example.com", 'Subject Line',  "Nothing to see here");
        $match = $I->grabFromLastEmailTo($user, "/Hello (World)/");
        $I->assertEquals($match, "Hello World");
    }
}
