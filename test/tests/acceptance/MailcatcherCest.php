<?php

class MailcatcherCest
{
    public function _after(\NoGuy $I) {
        // Cleared old emails from MailCatcher
        $I->resetEmails();
    }

    public function try_test_for_the_presense_of_a_string_in_the_body_of_an_email(\NoGuy $I)
    {
        $body = "Hello World!";
        mail('user@example.com', 'Subject Line', $body);
        $I->seeInLastEmail($body);
    }

    public function try_test_for_the_presense_of_a_string_the_last_email_to_an_address(\NoGuy $I)
    {
        $body = "Hello World!";
        $user = "userA@example.com";
        mail($user, 'Subject Line', $body);
        mail('userB@example.com', 'Subject Line', "Goodbye Word!");
        $I->seeInLastEmailTo($user, $body);
    }

    public function try_test_for_the_presense_of_a_string_in_the_subject_of_an_email(\NoGuy $I)
    {
        $body = "Hello World!";
        $subject = "Subject Line";
        $user = "userA@example.com";
        mail($user, $subject, $body);
        $I->seeInLastEmail($subject);
    }

    public function try_test_for_the_absense_of_a_string_in_the_last_email_to_an_address(\NoGuy $I)
    {
        $body = "Goodbye Word!";
        $user = "userA@example.com";
        mail($user, 'Subject Line',  "Hello World!");
        mail('userB@example.com', 'Subject Line', $body);
        $I->dontSeeInLastEmailTo($user, $body);
    }

    public function try_to_grab_matches_from_an_email(\NoGuy $I)
    {
        mail("user@example.com", 'Subject Line',  "Hello World!");
        $matches = $I->grabMatchesFromLastEmail("/Hello (World)/");
        $I->assertEquals($matches, array('Hello World', 'World'));
    }
}
