<?php

namespace Codeception\Module;

use Codeception\Module;
use Codeception\Util\Email;
use GuzzleHttp\Client;
use ZBateson\MailMimeParser\Message;

class MailCatcher extends Module
{
    /**
     * @var Client
     */
    protected $mailcatcher;

    /**
     * @var array
     */
    protected $config = ['url', 'port', 'guzzleRequestOptions'];

    /**
     * @var array
     */
    protected $requiredFields = ['url', 'port'];

    public function _initialize(): void
    {
        $base_uri = trim($this->config['url'], '/') . ':' . $this->config['port'];

        $guzzleConfig = [
            'base_uri' => $base_uri
        ];
        if (isset($this->config['guzzleRequestOptions'])) {
            $guzzleConfig = array_merge($guzzleConfig, $this->config['guzzleRequestOptions']);
        }

        $this->mailcatcher = new Client($guzzleConfig);
    }


    /**
     * Reset emails
     *
     * Clear all emails from mailcatcher. You probably want to do this before
     * you do the thing that will send emails
     *
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    public function resetEmails(): void
    {
        $this->mailcatcher->delete('/messages');
    }


    /**
     * See In Last Email
     *
     * Look for a string in the most recent email
     *
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    public function seeInLastEmail(string $expected): void
    {
        $email = $this->lastMessage();
        $this->seeInEmail($email, $expected);
    }

    /**
     * See In nth Email
     *
     * Look for a string in the nth email
     **/
    public function seeInNthEmail(int $nth, string $expected): void
    {
        $email = $this->nthMessage($nth);
        $this->seeInEmail($email, $expected);
    }

    /**
     * See sender in last Email
     *
     * Compare a string with the last email sender
     **/
    public function seeInLastEmailSender(string $expected): void
    {
        $email = $this->lastMessage();
        $this->seeInEmailSender($email, $expected);
    }
    
    /**
     * See sender in nth Email
     *
     * Compare a string with the last email sender
     **/
    public function seeInNthEmailSender(int $nth, string $expected): void
    {
        $email = $this->nthMessage($nth);
        $this->seeInEmailSender($email, $expected);
    }

    /**
     * See recipient in last Email
     *
     * Look for a string in the last email recipients
     **/
    public function seeInLastEmailRecipient(string $expected): void
    {
        $email = $this->lastMessage();
        $this->seeInEmailRecipient($email, $expected);
    }
    
    /**
     * See recipient in nth Email
     *
     * Look for a string in the nth email recipients
     **/
    public function seeInNthEmailRecipient(int $nth, string $expected): void
    {
        $email = $this->nthMessage($nth);
        $this->seeInEmailRecipient($email, $expected);
    }

    /**
     * See In Last Email subject
     *
     * Look for a string in the most recent email subject
     *
     * @author Antoine Augusti <antoine.augusti@gmail.com>
     **/
    public function seeInLastEmailSubject(string $expected): void
    {
        $email = $this->lastMessage();
        $this->seeInEmailSubject($email, $expected);
    }

    /**
     * See In nth Email subject
     *
     * Look for a string in the nth email subject
     **/
    public function seeInNthEmailSubject(int $nth, string $expected): void
    {
        $email = $this->nthMessage($nth);
        $this->seeInEmailSubject($email, $expected);
    }

    /**
     * Don't See In Last Email subject
     *
     * Look for the absence of a string in the most recent email subject
     **/
    public function dontSeeInLastEmailSubject(string $expected): void
    {
        $email = $this->lastMessage();
        $this->dontSeeInEmailSubject($email, $expected);
    }

    /**
     * Don't See In nth Email subject
     *
     * Look for the absence of a string in the nth email subject
     **/
    public function dontSeeInNthEmailSubject(int $nth, string $expected): void
    {
        $email = $this->nthMessage($nth);
        $this->dontSeeInEmailSubject($email, $expected);
    }

    /**
     * Don't See In Last Email
     *
     * Look for the absence of a string in the most recent email
     **/
    public function dontSeeInLastEmail(string $unexpected): void
    {
        $email = $this->lastMessage();
        $this->dontSeeInEmail($email, $unexpected);
    }

    /**
     * Don't See In nth Email
     *
     * Look for the absence of a string in the nth email
     **/
    public function dontSeeInNthEmail(int $nth, string $unexpected): void
    {
        $email = $this->nthMessage($nth);
        $this->dontSeeInEmail($email, $unexpected);
    }

    /**
     * See In Last Email To
     *
     * Look for a string in the most recent email sent to $address
     *
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    public function seeInLastEmailTo(string $address, string $expected): void
    {
        $email = $this->lastMessageTo($address);
        $this->seeInEmail($email, $expected);
    }

    /**
     * See In nth Email To
     *
     * Look for a string in the nth email sent to $address
     **/
    public function seeInNthEmailTo(int $nth, string $address, string $expected): void
    {
        $email = $this->nthMessageTo($nth, $address);
        $this->seeInEmail($email, $expected);
    }

    /**
     * Don't See In Last Email To
     *
     * Look for the absence of a string in the most recent email sent to $address
     **/
    public function dontSeeInLastEmailTo(string $address, string $unexpected): void
    {
        $email = $this->lastMessageTo($address);
        $this->dontSeeInEmail($email, $unexpected);
    }
    
    /**
     * Don't See In nth Email To
     *
     * Look for the absence of a string in the nth email sent to $address
     **/
    public function dontSeeInNthEmailTo(int $nth, string $address, string $unexpected): void
    {
        $email = $this->nthMessageTo($nth, $address);
        $this->dontSeeInEmail($email, $unexpected);
    }

    /**
     * See In Last Email Subject To
     *
     * Look for a string in the most recent email subject sent to $address
     *
     * @author Antoine Augusti <antoine.augusti@gmail.com>
     **/
    public function seeInLastEmailSubjectTo(string $address, string $expected): void
    {
        $email = $this->lastMessageTo($address);
        $this->seeInEmailSubject($email, $expected);
    }

    /**
     * See In nth Email Subject To
     *
     * Look for a string in the nth email subject sent to $address
     **/
    public function seeInNthEmailSubjectTo(int $nth, string $address, string $expected): void
    {
        $email = $this->nthMessageTo($nth, $address);
        $this->seeInEmailSubject($email, $expected);
    }

    /**
     * Don't See In Last Email Subject To
     *
     * Look for the absence of a string in the most recent email subject sent to $address
     **/
    public function dontSeeInLastEmailSubjectTo(string $address, string $unexpected): void
    {
        $email = $this->lastMessageTo($address);
        $this->dontSeeInEmailSubject($email, $unexpected);
    }

    /**
     * Don't See In nth Email Subject To
     *
     * Look for the absence of a string in the nth email subject sent to $address
     **/
    public function dontSeeInNthEmailSubjectTo(int $nth, string $address, string $unexpected): void
    {
        $email = $this->nthMessageTo($nth, $address);
        $this->dontSeeInEmailSubject($email, $unexpected);
    }

    public function lastMessage(): \Codeception\Util\Email
    {
        $messages = $this->messages();
        if (empty($messages)) {
            $this->fail("No messages received");
        }

        $last = array_shift($messages);

        return $this->emailFromId($last['id']);
    }
    
    public function nthMessage(int $nth): \Codeception\Util\Email
    {
        if ($nth < 1) {
            $this->fail("nth must be greater than zero");
        }

        // Last message is the first (ordered by date ASC)
        $messages = array_reverse($this->messages());
        if (empty($messages)) {
            $this->fail("No messages received");
        }

        if (!isset($messages[$nth - 1])) {
            $this->fail("No message found at location {$nth}");
        }

        return $this->emailFromId($messages[$nth - 1]['id']);
    }

    public function lastMessageTo(string $address): \Codeception\Util\Email
    {
        $ids = [];
        $messages = $this->messages();
        if (empty($messages)) {
            $this->fail("No messages received");
        }

        foreach ($messages as $message) {
            foreach ($message['recipients'] as $recipient) {
                if (strpos($recipient, $address) !== false) {
                    $ids[] = $message['id'];
                }
            }
        }

        if (count($ids) === 0) {
            $this->fail("No messages sent to {$address}");
        }

        return $this->emailFromId(max($ids));
    }

    public function nthMessageTo(int $nth, string $address): \Codeception\Util\Email
    {
        if ($nth < 1) {
            $this->fail("nth must be greater than zero");
        }

        $ids = [];
        // Last message is the first (ordered by date ASC)
        $messages = array_reverse($this->messages());
        if (empty($messages)) {
            $this->fail("No messages received");
        }

        foreach ($messages as $message) {
            foreach ($message['recipients'] as $recipient) {
                if (strpos($recipient, $address) !== false) {
                    $ids[] = $message['id'];
                }
            }
        }

        if (count($ids) === 0) {
            $this->fail("No messages sent to {$address}");
        }

        if (!isset($ids[$nth - 1])) {
            $this->fail("No message found at location {$nth} sent to {$address}");
        }

        return $this->emailFromId($ids[$nth - 1]);
    }

    public function lastMessageFrom(string $address): \Codeception\Util\Email
    {
        $ids = [];
        $messages = $this->messages();
        if (empty($messages)) {
            $this->fail("No messages received");
        }

        foreach ($messages as $message) {
            if (strpos($message['sender'], $address) !== false) {
                $ids[] = $message['id'];
            }

            // @todo deprecated, remove
            foreach ($message['recipients'] as $recipient) {
                if (strpos($recipient, $address) !== false) {
                    trigger_error('`lastMessageFrom` no longer accepts a recipient email.', E_USER_DEPRECATED);
                    $ids[] = $message['id'];
                }
            }
        }

        if (count($ids) === 0) {
            $this->fail("No messages sent from {$address}");
        }

        return $this->emailFromId(max($ids));
    }
    
    public function nthMessageFrom(int $nth, string $address): \Codeception\Util\Email
    {
        $ids = [];
        $messages = $this->messages();
        if (empty($messages)) {
            $this->fail("No messages received");
        }

        foreach ($messages as $message) {
            if (strpos($message['sender'], $address) !== false) {
                $ids[] = $message['id'];
            }

            // @todo deprecated, remove
            foreach ($message['recipients'] as $recipient) {
                if (strpos($recipient, $address) !== false) {
                    trigger_error('`lastMessageFrom` no longer accepts a recipient email.', E_USER_DEPRECATED);
                    $ids[] = $message['id'];
                }
            }
        }

        if (count($ids) === 0) {
            $this->fail("No messages sent from {$address}");
        }

        return $this->emailFromId($ids[$nth - 1]);
    }

    /**
     * Grab Matches From Last Email
     *
     * Look for a regex in the email source and return it's matches
     *
     * @author Stephan Hochhaus <stephan@yauh.de>
     * @return mixed[]
     **/
    public function grabMatchesFromLastEmail(string $regex): array
    {
        $email = $this->lastMessage();
        return $this->grabMatchesFromEmail($email, $regex);
    }
    
    /**
     * Grab Matches From Nth Email
     *
     * Look for a regex in the email source and return it's matches
     *
     * @return mixed[]
     **/
    public function grabMatchesFromNthEmail(int $nth, string $regex): array
    {
        $email = $this->nthMessage($nth);
        return $this->grabMatchesFromEmail($email, $regex);
    }

    /**
     * Grab From Last Email
     *
     * Look for a regex in the email source and return it
     *
     * @author Stephan Hochhaus <stephan@yauh.de>
     **/
    public function grabFromLastEmail(string $regex): string
    {
        $matches = $this->grabMatchesFromLastEmail($regex);
        return $matches[0];
    }
    
    /**
     * Grab From Nth Email
     *
     * Look for a regex in the email source and return it
     **/
    public function grabFromNthEmail(int $nth, string $regex): string
    {
        $matches = $this->grabMatchesFromNthEmail($nth, $regex);
        return $matches[0];
    }

    /**
     * Grab Matches From Last Email To
     *
     * Look for a regex in most recent email sent to $addres email source and
     * return it's matches
     *
     * @author Stephan Hochhaus <stephan@yauh.de>
     * @return mixed[]
     **/
    public function grabMatchesFromLastEmailTo(string $address, string $regex): array
    {
        $email = $this->lastMessageTo($address);
        return $this->grabMatchesFromEmail($email, $regex);
    }
    
    /**
     * Grab Matches From Nth Email To
     *
     * Look for a regex in the nth email sent to $addres email source and
     * return it's matches
     *
     * @return mixed[]
     **/
    public function grabMatchesFromNthEmailTo(int $nth, string $address, string $regex): array
    {
        $email = $this->nthMessageTo($nth, $address);
        return $this->grabMatchesFromEmail($email, $regex);
    }

    /**
     * Grab From Last Email To
     *
     * Look for a regex in most recent email sent to $addres email source and
     * return it
     *
     * @author Stephan Hochhaus <stephan@yauh.de>
     **/
    public function grabFromLastEmailTo(string $address, string $regex): string
    {
        $matches = $this->grabMatchesFromLastEmailTo($address, $regex);
        return $matches[0];
    }

    /**
     * Grab From Last Email To
     *
     * Look for a regex in the nth email sent to $addres email source and
     * return it
     **/
    public function grabFromNthEmailTo(int $nth, string $address, string $regex): string
    {
        $matches = $this->grabMatchesFromNthEmailTo($nth, $address, $regex);
        return $matches[0];
    }
    
    /**
     * Grab Urls From Email
     *
     * Return the urls the email contains
     *
     * @author Marcelo Briones <ing@marcelobriones.com.ar>
     * @return mixed[]
     */
    public function grabUrlsFromLastEmail(): array
    {
        $email = $this->lastMessage();
        
        return $this->grabUrlsFromEmail($email);
    }
    
    /**
     * Grab Urls From Email
     *
     * Return the urls the email contains
     *
     * @return mixed[]
     */
    public function grabUrlsFromNthEmail(int $nth): array
    {
        $email = $this->nthMessage($nth);

        return $this->grabUrlsFromEmail($email);
    }

    protected function grabUrlsFromEmail(Email $email): array
    {
        $regex = '#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#';
        $message = Message::from($email->getSource());

        $text = $message->getTextContent();
        preg_match_all($regex, $text, $text_matches);

        $html = $message->getHtmlContent();
        preg_match_all($regex, $html, $html_matches);

        return array_merge($text_matches[0], $html_matches[0]);
    }

    /**
     * Grab Attachments From Email
     *
     * Returns array with the format [ [filename1 => bytes1], [filename2 => bytes2], ...]
     *
     * @return array<string, string>
     * @author Marcelo Briones <ing@marcelobriones.com.ar>
     */
    public function grabAttachmentsFromLastEmail(): array
    {
        $email = $this->lastMessage();

        $message = Message::from($email->getSource());

        $attachments = [];

        foreach ($message->getAllAttachmentParts() as $attachmentPart) {
            $filename = $attachmentPart->getFilename();
            $content = $attachmentPart->getContent();
            $attachments[$filename] = $content;
        }

        return $attachments;
    }

    /**
     * Grab Attachments From Email
     *
     * Returns array with the format [ [filename1 => bytes1], [filename2 => bytes2], ...]
     *
     * @return array<string, string>
     */
    public function grabAttachmentsFromNthEmail(int $nth): array
    {
        $email = $this->nthMessage($nth);

        $message = Message::from($email->getSource());

        $attachments = [];

        foreach ($message->getAllAttachmentParts() as $attachmentPart) {
            $filename = $attachmentPart->getFilename();
            $content = $attachmentPart->getContent();
            $attachments[$filename] = $content;
        }

        return $attachments;
    }

    /**
     * See Attachment In Last Email
     *
     * Look for a attachement with certain filename in the most recent email
     *
     * @author Marcelo Briones <ing@marcelobriones.com.ar>
     **/
    public function seeAttachmentInLastEmail(string $expectedFilename): void
    {
        $this->seeAttachmentInEmail($this->lastMessage(), $expectedFilename);
    }
    
    /**
     * See Attachment In Nth Email
     *
     * Look for a attachement with certain filename in the most recent email
     **/
    public function seeAttachmentInNthEmail(int $nth, string $expectedFilename): void
    {
        $this->seeAttachmentInEmail($this->nthMessage($nth), $expectedFilename);
    }
    
    protected function seeAttachmentInEmail(Email $email, string $expectedFilename): void
    {
        $message = Message::from($email->getSource());

        foreach ($message->getAllAttachmentParts() as $attachmentPart) {
            if ($attachmentPart->getFilename() === $expectedFilename) {
                return;
            }
        }
        $this->fail("Filename not found in attachments.");
    }

    /**
     * Test email count equals expected value
     *
     * @author Mike Crowe <drmikecrowe@gmail.com>
     **/
    public function seeEmailCount(int $expected): void
    {
        $messages = $this->messages();
        $count = count($messages);
        $this->assertEquals($expected, $count);
    }

    /**
     * Checks expected count of attachment in last email.
     *
     * @author Marcelo Briones <ing@marcelobriones.com.ar>
     **/
    public function seeEmailAttachmentCount(int $expectedCount): void
    {
        $email = $this->lastMessage();
        $message = Message::from($email->getSource());
        $this->assertEquals($expectedCount, $message->getAttachmentCount());
    }
    
    /**
     * Checks expected count of attachment in the nth email.
     **/
    public function seeNthEmailAttachmentCount(int $nth, int $expectedCount): void
    {
        $email = $this->nthMessage($nth);
        $message = Message::from($email->getSource());
        $this->assertEquals($expectedCount, $message->getAttachmentCount());
    }

    // ----------- HELPER METHODS BELOW HERE -----------------------//
    /**
     * Messages
     *
     * Get an array of all the message objects
     *
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    protected function messages(): array
    {
        $response = $this->mailcatcher->get('/messages');
        $messages = json_decode($response->getBody(), true);
        // Ensure messages are shown in the order they were recieved
        // https://github.com/sj26/mailcatcher/pull/184
        usort($messages, function ($messageA, $messageB): int {
            $sortKeyA = $messageA['created_at'] . $messageA['id'];
            $sortKeyB = $messageB['created_at'] . $messageB['id'];
            return ($sortKeyA > $sortKeyB) ? -1 : 1;
        });
        return $messages;
    }

    /**
     * @param int|string $id
     */
    protected function emailFromId($id): \Codeception\Util\Email
    {
        $response = $this->mailcatcher->get("/messages/{$id}.json");
        $plainMessage = $this->mailcatcher->get("/messages/{$id}.source");
        $messageData = json_decode($response->getBody(), true);
        $messageData['source'] = $plainMessage->getBody()->getContents();

        return Email::createFromMailcatcherData($messageData);
    }
    
    protected function seeInEmailSender(Email $email, string $expected): void
    {
        if(method_exists($this, 'assertStringContainsString')){
            $this->assertStringContainsString($expected, $email->getSender(), "Email Sender Contains");
        }else{
            $this->assertContains($expected, $email->getSender(), "Email Sender Contains");
        }
    }
    
    protected function seeInEmailRecipient(Email $email, string $expected): void
    {
        foreach ($email->getRecipients() as $recipient) {
            if (method_exists($this, 'assertStringContainsString')) {
                $this->assertStringContainsString($expected, $recipient, "Email Recipient Contains");
            } else {
                $this->assertContains($expected, $recipient, "Email Recipient Contains");
            }
        }
    }

    protected function seeInEmailSubject(Email $email, string $expected): void
    {
        if(method_exists($this, 'assertStringContainsString')){
            $this->assertStringContainsString($expected, $email->getSubject(), "Email Subject Contains");
        }else{
            $this->assertContains($expected, $email->getSubject(), "Email Subject Contains");
        }
    }

    protected function dontSeeInEmailSubject(Email $email, string $unexpected): void
    {
        if(method_exists($this, 'assertStringContainsString')){
            $this->assertStringNotContainsString($unexpected, $email->getSubject(), "Email Subject Does Not Contain");
        }else{
            $this->assertNotContains($unexpected, $email->getSubject(), "Email Subject Does Not Contain");
        }
    }

    protected function seeInEmail(Email $email, string $expected): void
    {
        if(method_exists($this, 'assertStringContainsString')){
            $this->assertStringContainsString($expected, $email->getSourceQuotedPrintableDecoded(), "Email Contains");
        }else{
            $this->assertContains($expected, $email->getSourceQuotedPrintableDecoded(), "Email Contains");
        }
    }

    protected function dontSeeInEmail(Email $email, string $unexpected): void
    {
        if(method_exists($this, 'assertStringContainsString')){
            $this->assertStringNotContainsString($unexpected, $email->getSourceQuotedPrintableDecoded(), "Email Does Not Contain");
        }else{
            $this->assertNotContains($unexpected, $email->getSourceQuotedPrintableDecoded(), "Email Does Not Contain");
        }
    }

    protected function grabMatchesFromEmail(Email $email, string $regex): array
    {
        preg_match($regex, $email->getSourceQuotedPrintableDecoded(), $matches);
        $this->assertNotEmpty($matches, "No matches found for $regex");
        return $matches;
    }
}
