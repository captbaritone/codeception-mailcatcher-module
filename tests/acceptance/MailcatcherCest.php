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
    
    public function test_see_in_last_email_sender(AcceptanceTester $I)
    {
        $sender = "senderB@example.com";
        $I->sendEmail('user@example.com', 'Subject Line', 'Hello World!');
        $I->sendEmail('user@example.com', 'Subject Line', 'Hello World!');
        $I->sendEmail('user@example.com', 'Subject Line', 'Hello World!', false, null, [], $sender);
        $I->seeInLastEmailSender($sender);
    }
    
    public function test_see_in_nth_email_sender(AcceptanceTester $I)
    {
        $sender = "senderB@example.com";
        $I->sendEmail('user@example.com', 'Subject Line', 'Hello World!');
        $I->sendEmail('user@example.com', 'Subject Line', 'Hello World!', false, null, [], $sender);
        $I->sendEmail('user@example.com', 'Subject Line', 'Hello World!');
        $I->seeInNthEmailSender(2, $sender);
    }
    
    public function test_see_in_last_email_recipient(AcceptanceTester $I)
    {
        $recipient = 'user@example.com';
        $I->sendEmail('userA@example.com', 'Subject Line', 'Hello World!');
        $I->sendEmail('userC@example.com', 'Subject Line', 'Hello World!');
        $I->sendEmail($recipient, 'Subject Line', 'Hello World!');
        $I->seeInLastEmailRecipient($recipient);
    }

    public function test_see_in_nth_email_recipient(AcceptanceTester $I)
    {
        $recipient = 'user@example.com';
        $I->sendEmail('userA@example.com', 'Subject Line', 'Hello World!');
        $I->sendEmail($recipient, 'Subject Line', 'Hello World!');
        $I->sendEmail('userC@example.com', 'Subject Line', 'Hello World!');
        $I->seeInNthEmailRecipient(2, $recipient);
    }

    public function test_see_in_last_email(AcceptanceTester $I)
    {
        $body = "Hello World!";
        $I->sendEmail('user@example.com', 'Subject Line', $body);
        $I->seeInLastEmail($body);
    }
    
    public function test_see_in_nth_email(AcceptanceTester $I)
    {
        $body = "Hello Codeception!";
        $I->sendEmail('user@example.com', 'Subject Line', "Hello World!");
        $I->sendEmail('user2@example.com', 'Subject Line', $body);
        $I->sendEmail('user3@example.com', 'Subject Line', "Hello World!");
        $I->seeInNthEmail(2, $body);
    }

    public function test_see_in_last_email_subject(AcceptanceTester $I)
    {
        $subject = 'Subject Line';
        $I->sendEmail('user@example.com', $subject, "Hello World!");
        $I->seeInLastEmailSubject($subject);
    }
    
    public function test_see_in_nth_email_subject(AcceptanceTester $I)
    {
        $subject = 'Hello Codeception!';
        $I->sendEmail('user@example.com', 'Subject Line', "Hello World!");
        $I->sendEmail('user@example.com', $subject, "Hello World!");
        $I->sendEmail('user@example.com', $subject, "Hello World!");
        $I->seeInNthEmailSubject(2, $subject);
    }

    public function test_dont_see_in_last_email_subject(AcceptanceTester $I)
    {
        $subject = 'Subject Line';
        $I->sendEmail('user@example.com', $subject, "Hello World!");
        $I->sendEmail('user@example.com', 'Another Subject', "Hello World!");
        $I->dontSeeInLastEmailSubject($subject);
    }
    
    public function test_dont_see_in_nth_email_subject(AcceptanceTester $I)
    {
        $subject = 'Hello Codeception!';
        $I->sendEmail('user@example.com', 'Another Subject', "Hello World!");
        $I->sendEmail('user@example.com', $subject, "Hello World!");
        $I->sendEmail('user@example.com', 'Another Subject', "Hello World!");
        $I->dontSeeInNthEmailSubject(3, $subject);
    }

    public function test_dont_see_in_last_email(AcceptanceTester $I)
    {
        $body = "Hello World!";
        $I->sendEmail('user@example.com', 'Subject Line', $body);
        $I->sendEmail('user@example.com', 'Subject Line', "Goodbye World!");
        $I->dontSeeInLastEmail($body);
    }
    
    public function test_dont_see_in_nth_email(AcceptanceTester $I)
    {
        $body = "Hello Codeception!";
        $I->sendEmail('user@example.com', 'Subject Line', "Hello World!");
        $I->sendEmail('user@example.com', 'Subject Line', $body);
        $I->sendEmail('user@example.com', 'Subject Line', "Hello World!");
        $I->dontSeeInNthEmail(1, $body);
        $I->dontSeeInNthEmail(3, $body);
    }

    public function test_see_in_last_email_to(AcceptanceTester $I)
    {
        $body = "Hello World!";
        $user = "userA@example.com";
        $I->sendEmail($user, 'Subject Line', $body);
        $I->sendEmail('userB@example.com', 'Subject Line', "Goodbye Word!");
        $I->seeInLastEmailTo($user, $body);
    }
    
    public function test_see_in_nth_email_to(AcceptanceTester $I)
    {
        $body = "Hello Codeception!";
        $user = "userA@example.com";
        $I->sendEmail($user, 'Subject Line', $body);
        $I->sendEmail('userB@example.com', 'Subject Line', "Goodbye Word!");
        $I->sendEmail($user, 'Subject Line', $body);
        // 2 = the second email sent to userA
        $I->seeInNthEmailTo(2, $user, $body);
    }

    public function test_dont_see_in_last_email_to(AcceptanceTester $I)
    {
        $body = "Goodbye World!";
        $user = "userA@example.com";
        $I->sendEmail($user, 'Subject Line',  "Hello World!");
        $I->sendEmail('userB@example.com', 'Subject Line', $body);
        $I->dontSeeInLastEmailTo($user, $body);
    }
    
    public function test_dont_see_in_nth_email_to(AcceptanceTester $I)
    {
        $body = "Goodbye World!";
        $user = "userA@example.com";
        $I->sendEmail($user, 'Subject Line',  "Hello World!");
        $I->sendEmail('userB@example.com', 'Subject Line', $body);
        $I->sendEmail($user, 'Subject Line',  "Hello World!");
        $I->sendEmail($user, 'Subject Line',  "Hello World!");
        $I->dontSeeInNthEmailTo(3, $user, $body);
    }

    public function test_see_in_last_email_subject_to(AcceptanceTester $I)
    {
        $subject = 'Subject Line';
        $user = "userA@example.com";
        $I->sendEmail($user, $subject, "Hello World!");
        $I->sendEmail('userB@example.com', 'Subject Line', "Goodbye Word!");
        $I->seeInLastEmailSubjectTo($user, $subject);
    }
    
    public function test_see_in_nth_email_subject_to(AcceptanceTester $I)
    {
        $subject = 'Subject Line';
        $user = "userA@example.com";
        $I->sendEmail($user, 'Hello World!', 'Hello World!');
        $I->sendEmail('userB@example.com', 'Subject Line', 'Goodbye Word!');
        $I->sendEmail($user, 'Hello World!', "Hello World!");
        $I->sendEmail($user, $subject, 'Hello World!');
        // 3 = the third email sent to userA
        $I->seeInNthEmailSubjectTo(3, $user, $subject);
    }

    public function test_dont_see_in_last_email_subject_to(AcceptanceTester $I)
    {
        $subject = "Subject Line";
        $user = "userA@example.com";
        $I->sendEmail($user, 'Nothing to see here', "Hello World!");
        $I->sendEmail('userB@example.com', $subject, "Hello World!");
        $I->dontSeeInLastEmailSubjectTo($user, $subject);
    }
    
    public function test_dont_see_in_nth_email_subject_to(AcceptanceTester $I)
    {
        $subject = "Subject Line";
        $user = "userA@example.com";
        $I->sendEmail($user, 'Nothing to see here', "Hello World!");
        $I->sendEmail('userB@example.com', $subject, "Hello World!");
        $I->sendEmail($user, 'Nothing to see here', "Hello World!");
        $I->dontSeeInNthEmailSubjectTo(2, $user, $subject);
    }

    public function test_grab_matches_from_last_email(AcceptanceTester $I)
    {
        $I->sendEmail("user@example.com", 'Subject Line',  "Hello World!");
        $matches = $I->grabMatchesFromLastEmail("/Hello (World)/");
        $I->assertEquals($matches, array('Hello World', 'World'));
    }
    
    public function test_grab_matches_from_nth_email(AcceptanceTester $I)
    {
        $I->sendEmail("user@example.com", 'Subject Line',  "Hello World!");
        $I->sendEmail("user@example.com", 'Another subject Line',  "Hello Codeception!");
        $matches = $I->grabMatchesFromNthEmail(2, "/Hello (Codeception)/");
        $I->assertEquals($matches, array('Hello Codeception', 'Codeception'));
    }

    public function test_grab_from_last_email(AcceptanceTester $I)
    {
        $I->sendEmail("user@example.com", 'Subject Line',  "Hello World!");
        $match = $I->grabFromLastEmail("/Hello (World)/");
        $I->assertEquals($match, "Hello World");
    }
    
    public function test_grab_from_nth_email(AcceptanceTester $I)
    {
        $I->sendEmail("user@example.com", 'Subject Line',  "Hello World!");
        $I->sendEmail("userA@example.com", 'Another Subject Line',  "Hello World!");
        $I->sendEmail("userB@example.com", 'Subject Line',  "Hello Codeception!");
        $match = $I->grabFromNthEmail(3, "/Hello (Codeception)/");
        $I->assertEquals($match, "Hello Codeception");
    }

    public function test_grab_matches_from_last_email_to(AcceptanceTester $I)
    {
        $user = "user@example.com";
        $I->sendEmail($user, 'Subject Line',  "Hello World!");
        $I->sendEmail("userB@example.com", 'Subject Line',  "Nothing to see here");
        $matches = $I->grabMatchesFromLastEmailTo($user, "/Hello (World)/");
        $I->assertEquals($matches, array('Hello World', 'World'));
    }
    
    public function test_grab_matches_from_nth_email_to(AcceptanceTester $I)
    {
        $user = "user@example.com";
        $I->sendEmail($user, 'Subject Line',  "Hello World!");
        $I->sendEmail("userB@example.com", 'Subject Line',  "Nothing to see here");
        $I->sendEmail($user, 'Another Subject Line',  "Hello Codeception!");
        $matches = $I->grabMatchesFromNthEmailTo(2, $user, "/Hello (Codeception)/");
        $I->assertEquals($matches, array('Hello Codeception', 'Codeception'));
    }

    public function test_grab_from_last_email_to(AcceptanceTester $I)
    {
        $user = "user@example.com";
        $I->sendEmail($user, 'Subject Line',  "Hello World!");
        $I->sendEmail("userB@example.com", 'Subject Line',  "Nothing to see here");
        $match = $I->grabFromLastEmailTo($user, "/Hello (World)/");
        $I->assertEquals($match, "Hello World");
    }
    
    public function test_grab_from_nth_email_to(AcceptanceTester $I)
    {
        $user = "user@example.com";
        $I->sendEmail($user, 'Subject Line',  "Hello World!");
        $I->sendEmail("userB@example.com", 'Subject Line',  "Nothing to see here");
        $I->sendEmail($user, 'Subject Line',  "Hello World!");
        $I->sendEmail($user, 'Subject Line',  "Hello Codeception!");
        $match = $I->grabFromNthEmailTo(3, $user, "/Hello (Codeception)/");
        $I->assertEquals($match, "Hello Codeception");
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
    public function test_grab_urls_from_nth_email(
        AcceptanceTester $I,
        \Codeception\Example $example
    )
    {
        $user = "user@example.com";
        $I->sendEmail($user, 'Email with urls', "I have no URLs.");
        $I->sendEmail($user, 'Another Email with urls', "I'm in $example[0] .");
        $I->sendEmail($user, 'And Another Email with urls', "I certainly have no URLs");
        $urls = $I->grabUrlsFromNthEmail(2);

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
     */
    public function test_grab_urls_from_nth_html_email(
        AcceptanceTester $I
    )
    {
        $user = "user@example.com";
        $url = "http://example.com/list.php?page=56";
        $I->sendEmail($user, 'Html email with urls', "<html><body><a href='https://www.something.com'>Another Link</a></body></html>.", true);
        $I->sendEmail($user, 'Html email with urls', "<html><body><a href='$url'>My Link</a></body></html>.", true);
        $urls = $I->grabUrlsFromNthEmail(2);

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
     * @param \Codeception\Example $example
     * @example ["http://example.com/list.php?page=56", "7bit"]
     * @example ["http://example.com/list.php?page=56", "quoted-printable"]
     * @example ["http://example.com/list.php?page=56", "base64"]
     * @example ["http://example.com/list.php?page=56", "8bit"]
     * @example ["http://example.com/list.php?page=56", "binary"]
     */
    public function test_grab_urls_from_nth_email_with_encoding(
        AcceptanceTester $I,
        \Codeception\Example $example
    )
    {
        $user = "user@example.com";
        $I->sendEmail($user, 'Subject Line', "Nothing to see here");
        $I->sendEmail($user, 'Subject Line', "Nothing to see here");
        $I->sendEmail($user, 'Email with urls, ' . $example[1], "I'm in $example[0] .", $example[1]);
        $I->sendEmail($user, 'Subject Line', "Nothing to see here");
        $urls = $I->grabUrlsFromNthEmail(3);

        $I->assertEquals($example[0], $urls[0]);
    }

    /**
     * @param AcceptanceTester $I
     */
    public function test_grab_attachments_from_last_email(AcceptanceTester $I)
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
    public function test_grab_attachments_from_nth_email(AcceptanceTester $I)
    {
        $user = "user@example.com";

        $attachments = [
            "image.jpg" => codecept_data_dir('image.jpg'),
            "lorem.txt" => codecept_data_dir('lorem.txt'),
            "compressed.zip" => codecept_data_dir('compressed.zip'),
        ];

        $I->sendEmail($user, 'Email without attachments', "I have no attachments.");
        $I->sendEmail($user, 'Email with attachments', "I have attachments.", false, null, $attachments);
        $I->sendEmail($user, 'Email without attachments', "I have no attachments.");
        $grabbedAttachments = $I->grabAttachmentsFromNthEmail(2);

        $I->assertEquals(3, count($grabbedAttachments));
    }

    /**
     * @param AcceptanceTester $I
     */
    public function test_see_attachment_in_last_email(AcceptanceTester $I)
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
    public function test_see_attachment_in_nth_email(AcceptanceTester $I)
    {
        $user = "user@example.com";

        $attachments = [
            "image.jpg" => codecept_data_dir('image.jpg')
        ];

        $I->sendEmail($user, 'Email without attachments', "I have no attachments.");
        $I->sendEmail($user, 'Email with attachments', "I have attachments.", false, null, $attachments);
        $I->sendEmail($user, 'Email without attachments', "I have no attachments.");

        $I->seeAttachmentInNthEmail(2, "image.jpg");
    }

    /**
     * @param AcceptanceTester $I
     */
    public function test_fail_see_attachment_in_last_email(AcceptanceTester $I)
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
    public function test_attachment_count_in_last_email(AcceptanceTester $I)
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
    public function test_attachment_count_in_nth_email(AcceptanceTester $I)
    {
        $user = "user@example.com";

        $attachments = [
            "image.jpg" => codecept_data_dir('image.jpg'),
            "lorem.txt" => codecept_data_dir('lorem.txt'),
            "compressed.zip" => codecept_data_dir('compressed.zip'),
        ];

        $I->sendEmail($user, 'Email without attachments', "I have no attachments.");
        $I->sendEmail($user, 'Email without attachments', "I have no attachments.");
        $I->sendEmail($user, 'Email with attachments', "I have attachments.", false, null, $attachments);
        $I->seeNthEmailAttachmentCount(3, count($attachments));
    }

    /**
     * @param AcceptanceTester $I
     */
    public function test_attachment_count_in_no_attachment_last_email(AcceptanceTester $I)
    {
        $user = "user@example.com";

        $I->sendEmail($user, 'Email without attachments', "I don't have attachments.");
        $I->seeEmailAttachmentCount(0);
    }
    
    /**
     * @param AcceptanceTester $I
     */
    public function test_attachment_count_in_no_attachment_nth_email(AcceptanceTester $I)
    {
        $user = "user@example.com";

        $I->sendEmail($user, 'Email without attachments', "I don't have attachments.");
        $I->sendEmail($user, 'Email without attachments', "I don't have attachments.");
        $I->sendEmail($user, 'Email without attachments', "I don't have attachments.");
        $I->seeNthEmailAttachmentCount(2, 0);
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
}
