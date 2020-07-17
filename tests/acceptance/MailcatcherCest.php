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

    /**
     * @param AcceptanceTester $I
     * @param \Codeception\Example $example
     * @example ["http://localhost"]
     * @example ["http://localhost/"]
     * @example ["http://localhost.com"]
     * @example ["http://localhost.com/"]
     * @example ["http://localhost.com/index.html"]
     * @example ["http://localhost.com/index.php"]
     * @example ["http://localhost.com/index.php?token=123"]
     * @example ["http://localhost.com/index.php?auth&token=123"]
     * @example ["http://localhost.com/index.php?auth&id=12&token=123"]
     * @example ["http://example.com/list.php?page=56"]
     */
    public function test_grab_urls_from_last_email(
        AcceptanceTester $I,
        \Codeception\Example $example
    )
    {
        $user = "user@example.com";
        $I->sendEmail($user, 'Email with urls', "I'm in $example[0] .");
        $urls = $I->grabUrlsFromLastEmail();

        $I->assertEquals($example[0], $urls[0]);
    }

    /**
     * @param AcceptanceTester $I
     * @param \Codeception\Example $example
     * @example ["http://example.com/list.php?page=56", "7bit"]
     * @example ["http://example.com/list.php?page=56", "quoted-printable"]
     * @example ["http://example.com/list.php?page=56", "base64"]
     * @example ["http://example.com/list.php?page=56", "8bit"]
     * @example ["http://example.com/list.php?page=56", "binary"]
     */
    public function test_grab_urls_from_last_email_with_encoding(
        AcceptanceTester $I,
        \Codeception\Example $example
    )
    {
        $user = "user@example.com";
        $I->sendEmail($user, 'Email with urls, ' . $example[1], "I'm in $example[0] .", $example[1]);
        $urls = $I->grabUrlsFromLastEmail();

        $I->assertEquals($example[0], $urls[0]);
    }

    /**
     * @param AcceptanceTester $I
     */
    public function test_attachment_count_in_mail(AcceptanceTester $I)
    {
        $user = "user@example.com";

        $attachments = [
            "image.jpg" => codecept_data_dir('image.jpg'),
            "lorem.txt" => codecept_data_dir('lorem.txt'),
            "compressed.zip" => codecept_data_dir('compressed.zip'),
        ];

        $I->sendEmail($user, 'Email with attachments', "I have attachments.", null, $attachments);
        $I->seeEmailAttachmentCount(count($attachments));
    }

    /**
     * @param AcceptanceTester $I
     */
    public function test_attachment_count_in_no_attachment(AcceptanceTester $I)
    {
        $user = "user@example.com";

        $I->sendEmail($user, 'Email without attachments', "I don't have attachments.");
        $I->seeEmailAttachmentCount(0);
    }

    /**
     * @param AcceptanceTester $I
     */
    public function test_fail_attachment_count_in_mail(AcceptanceTester $I)
    {
        $user = "user@example.com";

        $attachments = [
            "image.jpg" => codecept_data_dir('image.jpg'),
        ];

        $I->sendEmail($user, 'Email with attachments', "I have attachments.", null, $attachments);

        try{
            $I->seeEmailAttachmentCount(3);
            $I->fail("seeEmailAttachmentCount should fail");
        } catch (Exception $e) {
            // test successful
        }
    }
}
