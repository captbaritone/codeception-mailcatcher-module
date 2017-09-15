<?php

class MailcatcherCest
{
    public function _before(AcceptanceTester $I) {
        // Clear old emails from MailCatcher
        $I->resetEmails();
    }

    public function test_reset_emails(AcceptanceTester $I)
    {
        $I->sendEmail('user@example.com', 'Subject Line', "Hello World!");
        $I->seeEmailCount(1);
        $I->resetEmails();
        $I->seeEmailCount(0);
    }

    public function test_see_in_last_email(AcceptanceTester $I)
    {
        $body = "Hello World!";
        $I->sendEmail('user@example.com', 'Subject Line', $body);
        $I->seeInLastEmail($body);
    }

    public function test_see_in_last_email_subject(AcceptanceTester $I)
    {
        $subject = 'Subject Line';
        $I->sendEmail('user@example.com', $subject, "Hello World!");
        $I->seeInLastEmailSubject($subject);
    }

    public function test_dont_see_in_last_email_subject(AcceptanceTester $I)
    {
        $subject = 'Subject Line';
        $I->sendEmail('user@example.com', $subject, "Hello World!");
        $I->sendEmail('user@example.com', 'Another Subject', "Hello World!");
        $I->dontSeeInLastEmailSubject($subject);
    }

    public function test_dont_see_in_last_email(AcceptanceTester $I)
    {
        $body = "Hello World!";
        $I->sendEmail('user@example.com', 'Subject Line', $body);
        $I->sendEmail('user@example.com', 'Subject Line', "Goodbye World!");
        $I->dontSeeInLastEmail($body);
    }

    public function test_see_in_last_email_to(AcceptanceTester $I)
    {
        $body = "Hello World!";
        $user = "userA@example.com";
        $I->sendEmail($user, 'Subject Line', $body);
        $I->sendEmail('userB@example.com', 'Subject Line', "Goodbye Word!");
        $I->seeInLastEmailTo($user, $body);
    }

    public function test_dont_see_in_last_email_to(AcceptanceTester $I)
    {
        $body = "Goodbye Word!";
        $user = "userA@example.com";
        $I->sendEmail($user, 'Subject Line',  "Hello World!");
        $I->sendEmail('userB@example.com', 'Subject Line', $body);
        $I->dontSeeInLastEmailTo($user, $body);
    }

    public function test_see_in_last_email_subject_to(AcceptanceTester $I)
    {
        $subject = 'Subject Line';
        $user = "userA@example.com";
        $I->sendEmail($user, $subject, "Hello World!");
        $I->sendEmail('userB@example.com', 'Subject Line', "Goodbye Word!");
        $I->seeInLastEmailSubjectTo($user, $subject);
    }

    public function test_dont_see_in_last_email_subject_to(AcceptanceTester $I)
    {
        $subject = "Subject Line";
        $user = "userA@example.com";
        $I->sendEmail($user, 'Nothing to see here', "Hello World!");
        $I->sendEmail('userB@example.com', $subject, "Hello World!");
        $I->dontSeeInLastEmailSubjectTo($user, $subject);
    }

    public function test_grab_matches_from_last_email(AcceptanceTester $I)
    {
        $I->sendEmail("user@example.com", 'Subject Line',  "Hello World!");
        $matches = $I->grabMatchesFromLastEmail("/Hello (World)/");
        $I->assertEquals($matches, array('Hello World', 'World'));
    }

    public function test_grab_from_last_email(AcceptanceTester $I)
    {
        $I->sendEmail("user@example.com", 'Subject Line',  "Hello World!");
        $match = $I->grabFromLastEmail("/Hello (World)/");
        $I->assertEquals($match, "Hello World");
    }

    public function test_grab_matches_from_last_email_to(AcceptanceTester $I)
    {
        $user = "user@example.com";
        $I->sendEmail($user, 'Subject Line',  "Hello World!");
        $I->sendEmail("userB@example.com", 'Subject Line',  "Nothing to see here");
        $matches = $I->grabMatchesFromLastEmailTo($user, "/Hello (World)/");
        $I->assertEquals($matches, array('Hello World', 'World'));
    }

    public function test_grab_from_last_email_to(AcceptanceTester $I)
    {
        $user = "user@example.com";
        $I->sendEmail($user, 'Subject Line',  "Hello World!");
        $I->sendEmail("userB@example.com", 'Subject Line',  "Nothing to see here");
        $match = $I->grabFromLastEmailTo($user, "/Hello (World)/");
        $I->assertEquals($match, "Hello World");
    }
    
    public function test_grab_attachment_from_email(AcceptanceTester $I) 
    {
        $I->sendEmailWithAttachment("user@example.com", 'Subject Line',  "Hello World!");
        $email = $I->lastMessageFrom("user@example.com");
        $content = $I->grabAttachmentFromEmail($email);
        $I->assertNotNull($content);
    }
}
