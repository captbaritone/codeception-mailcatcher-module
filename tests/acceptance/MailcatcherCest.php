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
     *
     * @example ["https://localhost"]
     * @example ["https://localhost/"]
     * @example ["https://localhost.com"]
     * @example ["https://localhost.com/"]
     * @example ["https://localhost.com/index.html"]
     * @example ["https://localhost.com/index.php"]
     * @example ["https://localhost.com/index.php?token=123"]
     * @example ["https://localhost.com/index.php?auth&token=123"]
     * @example ["https://localhost.com/index.php?auth&id=12&token=123"]
     * @example ["https://example.com/list.php?page=56"]
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
     */
    public function test_grab_urls_from_html_email(
        AcceptanceTester $I
    )
    {
        $user = "user@example.com";
        $url = "http://example.com/list.php?page=56";
        $I->sendEmail($user, 'Html email with urls', "<html><body><a href='$url'>My Link</a></body></html>.", true);
        $urls = $I->grabUrlsFromLastEmail();

        $I->assertEquals($url, $urls[0]);
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
    public function test_grab_attachments_from_last(AcceptanceTester $I)
    {
        $user = "user@example.com";

        $attachments = [
            "image.jpg" => codecept_data_dir('image.jpg'),
            "lorem.txt" => codecept_data_dir('lorem.txt'),
            "compressed.zip" => codecept_data_dir('compressed.zip'),
        ];

        $I->sendEmail($user, 'Email with attachments', "I have attachments.", false, null, $attachments);
        $grabbedAttachments = $I->grabAttachmentsFromLastEmail();

        $I->assertEquals(3, count($grabbedAttachments));
    }

    /**
     * @param AcceptanceTester $I
     */
    public function test_see_attachment_in_last(AcceptanceTester $I)
    {
        $user = "user@example.com";

        $attachments = [
            "image.jpg" => codecept_data_dir('image.jpg')
        ];

        $I->sendEmail($user, 'Email with attachments', "I have attachments.", false, null, $attachments);

        $I->seeAttachmentInLastEmail("image.jpg");
    }

    /**
     * @param AcceptanceTester $I
     */
    public function test_fail_see_attachment_in_last(AcceptanceTester $I)
    {
        $user = "user@example.com";

        $attachments = [
            "image.jpg" => codecept_data_dir('image.jpg')
        ];

        $I->sendEmail($user, 'Email with attachments', "I have attachments.", false, null, $attachments);

        $I->expectThrowable(new Exception("Filename not found in attachments."), function() use ($I) {
            $I->seeAttachmentInLastEmail("no.jpg");
        });
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

        $I->sendEmail($user, 'Email with attachments', "I have attachments.", false, null, $attachments);
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

        $I->sendEmail($user, 'Email with attachments', "I have attachments.", false, null, $attachments);

        $I->expectThrowable(new Exception("Failed asserting that 1 matches expected 3."), function() use ($I) {
            $I->seeEmailAttachmentCount(3);
        });
    }

    public function test_see_in_nth_email(AcceptanceTester $I)
    {
        $I->sendEmail('user@example.com', 'Subject Line', "First");
        $I->sendEmail('user@example.com', 'Subject Line', "Second");
        $I->sendEmail('user@example.com', 'Subject Line', "Third");
        $I->seeInNthEmail(2, "Second");
        $I->dontSeeInNthEmail(2, "Third");
    }

    public function test_see_in_nth_email_subject(AcceptanceTester $I)
    {
        $I->sendEmail('user@example.com', 'First Subject', "Hello World!");
        $I->sendEmail('user@example.com', 'Second Subject', "Hello World!");
        $I->sendEmail('user@example.com', 'Third Subject', "Hello World!");
        $I->seeInNthEmailSubject(2, 'Second Subject');
        $I->dontSeeInNthEmailSubject(2, 'Third Subject');
    }

    public function test_fail_nth_email_beyond_received_emails(AcceptanceTester $I)
    {
        $I->sendEmail('user@example.com', 'Subject Line', "Hello World!");
        $I->expectThrowable(new Exception("No message found at position 2"), function() use ($I) {
            $I->seeInNthEmail(2, "Hello World!");
        });
    }

    public function test_see_in_email_sender(AcceptanceTester $I)
    {
        $I->sendEmail('user@example.com', 'Subject Line', "Hello World!", false, null, [], 'first@example.com');
        $I->sendEmail('user@example.com', 'Subject Line', "Hello World!", false, null, [], 'second@example.com');
        $I->sendEmail('user@example.com', 'Subject Line', "Hello World!", false, null, [], 'third@example.com');
        $I->seeInLastEmailSender('third@example.com');
        $I->seeInNthEmailSender(2, 'second@example.com');
    }

    public function test_see_in_email_recipient(AcceptanceTester $I)
    {
        $I->sendEmail('userA@example.com', 'Subject Line', "Hello World!");
        $I->sendEmail('userB@example.com', 'Subject Line', "Hello World!");
        $I->sendEmail('userC@example.com', 'Subject Line', "Hello World!");
        $I->seeInLastEmailRecipient('userC@example.com');
        $I->seeInNthEmailRecipient(2, 'userB@example.com');
    }

    public function test_see_in_nth_email_to(AcceptanceTester $I)
    {
        $user = "userA@example.com";
        $I->sendEmail($user, 'First Subject', "First");
        $I->sendEmail('userB@example.com', 'Other Subject', "Other");
        $I->sendEmail($user, 'Second Subject', "Second");
        $I->sendEmail($user, 'Third Subject', "Third");
        $I->seeInNthEmailTo(2, $user, "Second");
        $I->dontSeeInNthEmailTo(2, $user, "Other");
        $I->seeInNthEmailSubjectTo(2, $user, 'Second Subject');
        $I->dontSeeInNthEmailSubjectTo(2, $user, 'Other Subject');
    }

    public function test_grab_from_nth_email(AcceptanceTester $I)
    {
        $I->sendEmail("user@example.com", 'Subject Line', "Hello World!");
        $I->sendEmail("user@example.com", 'Subject Line', "Hello Codeception!");
        $I->sendEmail("user@example.com", 'Subject Line', "Hello Again!");
        $I->assertEquals(['Hello Codeception', 'Codeception'], $I->grabMatchesFromNthEmail(2, "/Hello (Codeception)/"));
        $I->assertEquals('Hello Codeception', $I->grabFromNthEmail(2, "/Hello (Codeception)/"));
    }

    public function test_grab_from_nth_email_to(AcceptanceTester $I)
    {
        $user = "user@example.com";
        $I->sendEmail($user, 'Subject Line', "Hello World!");
        $I->sendEmail("userB@example.com", 'Subject Line', "Hello Other!");
        $I->sendEmail($user, 'Subject Line', "Hello Codeception!");
        $I->assertEquals(['Hello Codeception', 'Codeception'], $I->grabMatchesFromNthEmailTo(2, $user, "/Hello (\w+)/"));
        $I->assertEquals('Hello Codeception', $I->grabFromNthEmailTo(2, $user, "/Hello (\w+)/"));
    }

    public function test_grab_urls_from_nth_email(AcceptanceTester $I)
    {
        $url = "http://example.com/list.php?page=56";
        $I->sendEmail("user@example.com", 'Subject Line', "I have no URLs.");
        $I->sendEmail("user@example.com", 'Subject Line', "I'm in $url .");
        $I->sendEmail("user@example.com", 'Subject Line', "I'm in https://example.org .");
        $I->assertEquals([$url], $I->grabUrlsFromNthEmail(2));
    }

    public function test_attachments_in_nth_email(AcceptanceTester $I)
    {
        $user = "user@example.com";
        $attachments = [
            "image.jpg" => codecept_data_dir('image.jpg'),
            "lorem.txt" => codecept_data_dir('lorem.txt'),
        ];

        $I->sendEmail($user, 'Email without attachments', "I have no attachments.");
        $I->sendEmail($user, 'Email with attachments', "I have attachments.", false, null, $attachments);
        $I->sendEmail($user, 'Email without attachments', "I have no attachments.");

        $I->assertEquals(['image.jpg', 'lorem.txt'], array_keys($I->grabAttachmentsFromNthEmail(2)));
        $I->seeAttachmentInNthEmail(2, 'lorem.txt');
        $I->seeNthEmailAttachmentCount(2, 2);
        $I->seeNthEmailAttachmentCount(3, 0);
    }
}
