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
        mail('user@example.com', 'Subject Line', $body, 'From:no-reply@example.com');
        $I->seeInLastEmail($body);
    }

    public function test_see_in_last_email_subject(\NoGuy $I)
    {
        $subject = 'Subject Line';
        mail('user@example.com', $subject, "Hello World!", 'From:no-reply@example.com');
        $I->seeInLastEmailSubject($subject);
    }

    public function test_dont_see_in_last_email_subject(\NoGuy $I)
    {
        $subject = 'Subject Line';
        mail('user@example.com', $subject, "Hello World!", 'From:no-reply@example.com');
        mail('user@example.com', 'Another Subject', "Hello World!", 'From:no-reply@example.com');
        $I->dontSeeInLastEmailSubject($subject);
    }

    public function test_dont_see_in_last_email(\NoGuy $I)
    {
        $body = "Hello World!";
        mail('user@example.com', 'Subject Line', $body, 'From:no-reply@example.com');
        mail('user@example.com', 'Subject Line', "Goodbye World!", 'From:no-reply@example.com');
        $I->dontSeeInLastEmail($body);
    }

    public function test_see_in_last_email_to(\NoGuy $I)
    {
        $body = "Hello World!";
        $user = "userA@example.com";
        mail($user, 'Subject Line', $body, 'From:no-reply@example.com');
        mail('userB@example.com', 'Subject Line', "Goodbye Word!", 'From:no-reply@example.com');
        $I->seeInLastEmailTo($user, $body);
    }

    public function test_dont_see_in_last_email_to(\NoGuy $I)
    {
        $body = "Goodbye Word!";
        $user = "userA@example.com";
        mail($user, 'Subject Line',  "Hello World!", 'From:no-reply@example.com');
        mail('userB@example.com', 'Subject Line', $body, 'From:no-reply@example.com');
        $I->dontSeeInLastEmailTo($user, $body);
    }

    public function test_see_in_last_email_subject_to(\NoGuy $I)
    {
        $subject = 'Subject Line';
        $user = "userA@example.com";
        mail($user, $subject, "Hello World!", 'From:no-reply@example.com');
        mail('userB@example.com', 'Subject Line', "Goodbye Word!", 'From:no-reply@example.com');
        $I->seeInLastEmailSubjectTo($user, $subject);
    }

    public function test_dont_see_in_last_email_subject_to(\NoGuy $I)
    {
        $subject = "Subject Line";
        $user = "userA@example.com";
        mail($user, 'Nothing to see here', "Hello World!", 'From:no-reply@example.com');
        mail('userB@example.com', $subject, "Hello World!", 'From:no-reply@example.com');
        $I->dontSeeInLastEmailSubjectTo($user, $subject);
    }

    public function test_grab_matches_from_last_email(\NoGuy $I)
    {
        mail("user@example.com", 'Subject Line',  "Hello World!", 'From:no-reply@example.com');
        $matches = $I->grabMatchesFromLastEmail("/Hello (World)/");
        $I->assertEquals($matches, array('Hello World', 'World'));
    }

    public function test_grab_from_last_email(\NoGuy $I)
    {
        mail("user@example.com", 'Subject Line',  "Hello World!", 'From:no-reply@example.com');
        $match = $I->grabFromLastEmail("/Hello (World)/");
        $I->assertEquals($match, "Hello World");
    }

    public function test_grab_matches_from_last_email_to(\NoGuy $I)
    {
        $user = "user@example.com";
        mail($user, 'Subject Line',  "Hello World!", 'From:no-reply@example.com');
        mail("userB@example.com", 'Subject Line',  "Nothing to see here", 'From:no-reply@example.com');
        $matches = $I->grabMatchesFromLastEmailTo($user, "/Hello (World)/");
        $I->assertEquals($matches, array('Hello World', 'World'));
    }

    public function test_grab_from_last_email_to(\NoGuy $I)
    {
        $user = "user@example.com";
        mail($user, 'Subject Line',  "Hello World!", 'From:no-reply@example.com');
        mail("userB@example.com", 'Subject Line',  "Nothing to see here", 'From:no-reply@example.com');
        $match = $I->grabFromLastEmailTo($user, "/Hello (World)/");
        $I->assertEquals($match, "Hello World");
    }
}
