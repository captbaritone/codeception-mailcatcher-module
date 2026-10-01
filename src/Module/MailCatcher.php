<?php

namespace Codeception\Module;

use Codeception\Module;
use Codeception\Util\Email;
use GuzzleHttp\Client;
use ZBateson\MailMimeParser\Message;

class MailCatcher extends Module
{
    /**
     * @var array
     */
    protected array $config = ['url', 'port', 'guzzleRequestOptions'];

    protected Client $mailcatcher;

    protected array $requiredFields = ['url', 'port'];

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

    public function seeInNthEmail(int $nth, string $expected): void
    {
        $this->seeInEmail($this->nthMessage($nth), $expected);
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

    public function seeInNthEmailSubject(int $nth, string $expected): void
    {
        $this->seeInEmailSubject($this->nthMessage($nth), $expected);
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

    public function dontSeeInNthEmailSubject(int $nth, string $unexpected): void
    {
        $this->dontSeeInEmailSubject($this->nthMessage($nth), $unexpected);
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

    public function dontSeeInNthEmail(int $nth, string $unexpected): void
    {
        $this->dontSeeInEmail($this->nthMessage($nth), $unexpected);
    }

    public function seeInLastEmailSender(string $expected): void
    {
        $this->seeInEmailSender($this->lastMessage(), $expected);
    }

    public function seeInNthEmailSender(int $nth, string $expected): void
    {
        $this->seeInEmailSender($this->nthMessage($nth), $expected);
    }

    public function seeInLastEmailRecipient(string $expected): void
    {
        $this->seeInEmailRecipients($this->lastMessage(), $expected);
    }

    public function seeInNthEmailRecipient(int $nth, string $expected): void
    {
        $this->seeInEmailRecipients($this->nthMessage($nth), $expected);
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

    public function seeInNthEmailTo(int $nth, string $address, string $expected): void
    {
        $this->seeInEmail($this->nthMessageTo($nth, $address), $expected);
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

    public function dontSeeInNthEmailTo(int $nth, string $address, string $unexpected): void
    {
        $this->dontSeeInEmail($this->nthMessageTo($nth, $address), $unexpected);
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

    public function seeInNthEmailSubjectTo(int $nth, string $address, string $expected): void
    {
        $this->seeInEmailSubject($this->nthMessageTo($nth, $address), $expected);
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

    public function dontSeeInNthEmailSubjectTo(int $nth, string $address, string $unexpected): void
    {
        $this->dontSeeInEmailSubject($this->nthMessageTo($nth, $address), $unexpected);
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
        return $this->emailAtPosition($this->messagesInReceivedOrder(), $nth, "No message found at position {$nth}");
    }

    public function lastMessageTo(string $address): \Codeception\Util\Email
    {
        $ids = [];
        $messages = $this->messages();
        if (empty($messages)) {
            $this->fail("No messages received");
        }

        foreach ($messages as $message) {
            if ($this->isSentTo($message, $address)) {
                $ids[] = $message['id'];
            }
        }

        if (count($ids) === 0) {
            $this->fail("No messages sent to {$address}");
        }

        return $this->emailFromId(max($ids));
    }

    public function nthMessageTo(int $nth, string $address): \Codeception\Util\Email
    {
        $messages = array_filter(
            $this->messagesInReceivedOrder(),
            fn (array $message): bool => $this->isSentTo($message, $address)
        );
        if (empty($messages)) {
            $this->fail("No messages sent to {$address}");
        }

        return $this->emailAtPosition($messages, $nth, "No message found at position {$nth} sent to {$address}");
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
     * @return mixed[]
     */
    public function grabMatchesFromNthEmail(int $nth, string $regex): array
    {
        return $this->grabMatchesFromEmail($this->nthMessage($nth), $regex);
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

    public function grabFromNthEmail(int $nth, string $regex): string
    {
        return $this->grabMatchesFromNthEmail($nth, $regex)[0];
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
     * @return mixed[]
     */
    public function grabMatchesFromNthEmailTo(int $nth, string $address, string $regex): array
    {
        return $this->grabMatchesFromEmail($this->nthMessageTo($nth, $address), $regex);
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

    public function grabFromNthEmailTo(int $nth, string $address, string $regex): string
    {
        return $this->grabMatchesFromNthEmailTo($nth, $address, $regex)[0];
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
        return $this->grabUrlsFromEmail($this->lastMessage());
    }

    /**
     * @return string[]
     */
    public function grabUrlsFromNthEmail(int $nth): array
    {
        return $this->grabUrlsFromEmail($this->nthMessage($nth));
    }

    /**
     * Return the URL of the first link with an exact, case-sensitive label
     * in the last email's HTML body. Fail if no matching link has an href.
     */
    public function grabUrlForLinkFromLastEmail(string $label): string
    {
        return $this->grabUrlForLinkFromEmail($this->lastMessage(), $label);
    }

    /**
     * Return the URL of the first link with an exact, case-sensitive label
     * in the given email's HTML body. Fail if no matching link has an href.
     */
    public function grabUrlForLinkFromEmail(Email $email, string $label): string
    {
        $message = Message::from($email->getSource(), false);
        $html = $message->getHtmlContent();

        if ($html !== null && $html !== '') {
            $document = new \DOMDocument();
            $document->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);

            foreach ($document->getElementsByTagName('a') as $link) {
                if ($link->hasAttribute('href') && $link->textContent === $label) {
                    return $link->getAttribute('href');
                }
            }
        }

        $this->fail("No link found with label: {$label}");
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
        return $this->grabAttachmentsFromEmail($this->lastMessage());
    }

    /**
     * @return array<string, string>
     */
    public function grabAttachmentsFromNthEmail(int $nth): array
    {
        return $this->grabAttachmentsFromEmail($this->nthMessage($nth));
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

    public function seeAttachmentInNthEmail(int $nth, string $expectedFilename): void
    {
        $this->seeAttachmentInEmail($this->nthMessage($nth), $expectedFilename);
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
        $this->seeAttachmentCountInEmail($this->lastMessage(), $expectedCount);
    }

    public function seeNthEmailAttachmentCount(int $nth, int $expectedCount): void
    {
        $this->seeAttachmentCountInEmail($this->nthMessage($nth), $expectedCount);
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
        usort(
            $messages,
            static fn (array $messageA, array $messageB): int
                => [$messageB['created_at'], $messageB['id']] <=> [$messageA['created_at'], $messageA['id']]
        );
        return $messages;
    }

    protected function messagesInReceivedOrder(): array
    {
        $messages = $this->messages();
        if (empty($messages)) {
            $this->fail("No messages received");
        }

        return array_reverse($messages);
    }

    protected function emailAtPosition(array $messages, int $nth, string $notFoundMessage): \Codeception\Util\Email
    {
        if ($nth < 1) {
            $this->fail("Position must be 1 or greater, {$nth} given");
        }

        $message = array_values($messages)[$nth - 1] ?? null;
        if ($message === null) {
            $this->fail($notFoundMessage);
        }

        return $this->emailFromId($message['id']);
    }

    protected function isSentTo(array $message, string $address): bool
    {
        foreach ($message['recipients'] as $recipient) {
            if (str_contains($recipient, $address)) {
                return true;
            }
        }

        return false;
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

    protected function seeInEmailSubject(Email $email, string $expected): void
    {
        $this->assertStringContainsString($expected, $email->getSubject(), "Email Subject Contains");
    }

    protected function dontSeeInEmailSubject(Email $email, string $unexpected): void
    {
        $this->assertStringNotContainsString($unexpected, $email->getSubject(), "Email Subject Does Not Contain");
    }

    protected function seeInEmail(Email $email, string $expected): void
    {
        $this->assertStringContainsString($expected, $email->getSourceQuotedPrintableDecoded(), "Email Contains");
    }

    protected function dontSeeInEmail(Email $email, string $unexpected): void
    {
        $this->assertStringNotContainsString($unexpected, $email->getSourceQuotedPrintableDecoded(), "Email Does Not Contain");
    }

    protected function seeInEmailSender(Email $email, string $expected): void
    {
        $this->assertStringContainsString($expected, $email->getSender(), "Email Sender Contains");
    }

    protected function seeInEmailRecipients(Email $email, string $expected): void
    {
        $recipients = $email->getRecipients();
        $matchingRecipients = array_filter(
            $recipients,
            static fn (string $recipient): bool => str_contains($recipient, $expected)
        );

        $this->assertNotEmpty(
            $matchingRecipients,
            sprintf('Failed asserting that one of the email recipients [%s] contains "%s".', implode(', ', $recipients), $expected)
        );
    }

    protected function grabMatchesFromEmail(Email $email, string $regex): array
    {
        preg_match($regex, $email->getSourceQuotedPrintableDecoded(), $matches);
        $this->assertNotEmpty($matches, "No matches found for $regex");
        return $matches;
    }

    /**
     * @return string[]
     */
    protected function grabUrlsFromEmail(Email $email): array
    {
        $regex = '#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#';
        $message = Message::from($email->getSource(), false);

        preg_match_all($regex, $message->getTextContent() ?? '', $textMatches);
        preg_match_all($regex, $message->getHtmlContent() ?? '', $htmlMatches);

        return array_merge($textMatches[0], $htmlMatches[0]);
    }

    /**
     * @return array<string, string>
     */
    protected function grabAttachmentsFromEmail(Email $email): array
    {
        $attachments = [];
        foreach (Message::from($email->getSource(), false)->getAllAttachmentParts() as $attachmentPart) {
            $attachments[$attachmentPart->getFilename()] = $attachmentPart->getContent();
        }

        return $attachments;
    }

    protected function seeAttachmentInEmail(Email $email, string $expectedFilename): void
    {
        foreach (Message::from($email->getSource(), false)->getAllAttachmentParts() as $attachmentPart) {
            if ($attachmentPart->getFilename() === $expectedFilename) {
                return;
            }
        }
        $this->fail("Filename not found in attachments.");
    }

    protected function seeAttachmentCountInEmail(Email $email, int $expectedCount): void
    {
        $this->assertEquals($expectedCount, Message::from($email->getSource(), false)->getAttachmentCount());
    }
}
