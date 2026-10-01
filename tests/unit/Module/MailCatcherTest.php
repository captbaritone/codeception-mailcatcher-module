<?php

namespace Codeception\Util;

use Codeception\Module\MailCatcher;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\AssertionFailedError;

class MailCatcherTest extends \Codeception\Test\Unit
{
    public function testInitialize()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->_setConfig([
            'url' => 'http://my-mailcatcher',
            'port' => '1111',
            'guzzleRequestOptions' => ['someOption' => 'test']
        ]);

        $mailcatcher->_initialize();

        $this->assertEquals('test', $mailcatcher->getClient()->getConfig('someOption'));

        /** @var Uri $uri */
        $uri = $mailcatcher->getClient()->getConfig('base_uri');

        $this->assertEquals('my-mailcatcher', $uri->getHost());
        $this->assertEquals(1111, $uri->getPort());
    }

    public function testResetEmails()
    {
        $handler = new MockHandler([
            new Response(200)
        ]);
        $client = new Client(['handler' => $handler]);

        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setClient($client);

        $mailcatcher->resetEmails();

        $this->assertEquals('DELETE', $handler->getLastRequest()->getMethod());
        $this->assertEquals('/messages', $handler->getLastRequest()->getRequestTarget());
    }

    public function testLastMessageNoMessages()
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([]))
        ]);
        $client = new Client(['handler' => $handler]);

        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setClient($client);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No messages received');

        $mailcatcher->lastMessage();
    }

    public function testSeeInLastEmail()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, [], '', 'Test body and some more text'));

        $mailcatcher->seeInLastEmail('Test body');
    }

    public function testDontSeeInLastEmail()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, [], '', 'Body with test data'));

        $mailcatcher->dontSeeInLastEmail('Test body');
    }

    public function testSeeInLastEmailSubject()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, [], 'Test subject', ''));

        $mailcatcher->seeInLastEmailSubject('Test subject');
    }

    public function testDontSeeInLastEmailSubject()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, [], 'Test subject', ''));

        $mailcatcher->dontSeeInLastEmailSubject('Hello world');
    }

    public function testDontSeeInLastEmailSubjectWithoutSubject()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, [], null, ''));

        $mailcatcher->dontSeeInLastEmailSubject('Hello world');
    }

    public function testSeeInLastEmailSubjectWithoutSubjectFails()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, [], null, ''));

        $this->expectException(AssertionFailedError::class);

        $mailcatcher->seeInLastEmailSubject('Hello world');
    }

    public function testLastMessageToNoMessages()
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([]))
        ]);
        $client = new Client(['handler' => $handler]);

        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setClient($client);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No messages received');

        $mailcatcher->lastMessageTo('user2@example.com');
    }

    /**
     * Check that if we ask for messages from a specific email address, and we have
     * messages but not from them - that we report back accurately.
     *
     * @return void
     */
    public function testLastMessageFromNoMessages()
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([
                [
                    'id' => 1,
                    'created_at' => date('c'),
                    'sender' => 'sender@example.com',
                    'recipients' => ['user@example.com'],
                ],
            ]))
        ]);
        $client = new Client(['handler' => $handler]);

        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setClient($client);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No messages sent from user2@example.com');

        $mailcatcher->lastMessageFrom('user2@example.com');
    }

    /**
     * Check that we get the correct Last Message From even if it's neither the
     * newest or the oldest (to ensure we're not accidentally getting the right one)
     *
     * @return void
     */
    public function testLastMessageFrom()
    {
        $mailcatcher = $this->mailcatcherWithMessages(
            [
                $this->message(1, 'sender@example.com', ['user@example.com']),
                $this->message(2, 'sender2@example.com', ['user2@example.com']),
                $this->message(3, 'sender3@example.com', ['user3@example.com']),
            ],
            new Email(2, [], '', ''),
        );

        $this->assertEquals(2, $mailcatcher->lastMessageFrom('sender2@example.com')->getId());
    }

    public function testSeeInLastEmailTo()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessageTo(new Email(1, ['test@example.com'], '', 'Test body and some more text'));

        $mailcatcher->seeInLastEmailTo('test@example.com', 'Test body');
    }

    public function testDontSeeInLastEmailTo()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessageTo(new Email(1, ['test@example.com'], '', 'Body with test data'));

        $mailcatcher->dontSeeInLastEmailTo('test@example.com', 'Test body');
    }

    public function testSeeInLastEmailSubjectTo()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessageTo(new Email(1, ['test@example.com'], 'Test subject', ''));

        $mailcatcher->seeInLastEmailSubjectTo('test@example.com', 'Test subject');
    }

    public function testDontSeeInLastEmailSubjectTo()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessageTo(new Email(1, ['test@example.com'], 'Test subject', ''));

        $mailcatcher->dontSeeInLastEmailSubjectTo('test@example.com', 'Hello world');
    }

    public function testGrabUrlForLinkFromLastEmail()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, [], '',
            "Content-Type: text/html; charset=UTF-8\r\n\r\n"
            . '<a href="http://first-link.com">First link</a>'
            . '<a href="http://second-link.com">Second link</a>'
        ));

        $this->assertSame('http://second-link.com', $mailcatcher->grabUrlForLinkFromLastEmail('Second link'));
    }

    public function testGrabUrlForLinkFromEmail()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $email = new Email(1, [], '',
            "Content-Type: text/html; charset=UTF-8\r\n\r\n"
            . '<a href="http://first-link.com">First link</a>'
            . '<a href="http://second-link.com">Second link</a>'
        );

        $this->assertSame('http://second-link.com', $mailcatcher->grabUrlForLinkFromEmail($email, 'Second link'));
    }

    public function testGrabUrlForLinkFromGivenEmail()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(2, [], '',
            "Content-Type: text/html; charset=UTF-8\r\n\r\n"
            . '<a href="https://example.com/latest">Reset password</a>'
        ));
        $email = new Email(1, ['user@example.com'], '',
            "Content-Type: text/html; charset=UTF-8\r\n\r\n"
            . '<a href="https://example.com/selected">Reset password</a>'
        );

        $this->assertSame('https://example.com/selected', $mailcatcher->grabUrlForLinkFromEmail($email, 'Reset password'));
    }

    public function testGrabUrlForLinkFromEncodedHtmlEmail()
    {
        $html = '<a href="https://example.com/first?one=1&amp;two=2"><strong>Café &amp; "tea"</strong></a>'
            . '<a href="https://example.com/second">Café &amp; "tea"</a>';
        $mailcatcher = new MailCatcherTest_TestClass();
        $email = new Email(1, [], '',
            "MIME-Version: 1.0\r\nContent-Type: multipart/alternative; boundary=parts\r\n\r\n"
            . "--parts\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\nPlain text\r\n"
            . "--parts\r\nContent-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: quoted-printable\r\n\r\n"
            . quoted_printable_encode($html) . "\r\n--parts--\r\n"
        );

        $this->assertSame('https://example.com/first?one=1&two=2', $mailcatcher->grabUrlForLinkFromEmail($email, 'Café & "tea"'));
        $mailcatcher->setLastMessage($email);
        $this->assertSame('https://example.com/first?one=1&two=2', $mailcatcher->grabUrlForLinkFromLastEmail('Café & "tea"'));
    }

    /**
     * @dataProvider missingLinkEmails
     */
    public function testGrabUrlForLinkFromLastEmailFailsWhenNoLinkMatches(string $source)
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, [], '', $source));

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No link found with label: Second link');

        $mailcatcher->grabUrlForLinkFromLastEmail('Second link');
    }

    /**
     * @dataProvider missingLinkEmails
     */
    public function testGrabUrlForLinkFromEmailFailsWhenNoLinkMatches(string $source)
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $email = new Email(1, [], '', $source);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No link found with label: Second link');

        $mailcatcher->grabUrlForLinkFromEmail($email, 'Second link');
    }

    public static function missingLinkEmails(): array
    {
        return [
            'empty body' => ["Content-Type: text/html\r\n\r\n"],
            'plain text' => ["Content-Type: text/plain\r\n\r\n<a href='https://example.com'>Second link</a>"],
            'different label' => ["Content-Type: text/html\r\n\r\n<a href='https://example.com'>First link</a>"],
            'different case' => ["Content-Type: text/html\r\n\r\n<a href='https://example.com'>second link</a>"],
            'partial label' => ["Content-Type: text/html\r\n\r\n<a href='https://example.com'>Second link extra</a>"],
            'no href' => ["Content-Type: text/html\r\n\r\n<a>Second link</a>"],
        ];
    }

    public function testSeeEmailCount()
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([
                [
                    'id' => 1,
                    'created_at' => date('c'),
                    'recipients' => ['user@example.com'],
                ],
                [
                    'id' => 1,
                    'created_at' => date('c'),
                    'recipients' => ['user2@example.com'],
                ]
            ]))
        ]);
        $client = new Client(['handler' => $handler]);

        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setClient($client);

        $mailcatcher->seeEmailCount(2);
    }

    public function testSeeEmailCountFail()
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([
                [
                    'id' => 1,
                    'created_at' => date('c'),
                    'recipients' => ['user@example.com'],
                ],
                [
                    'id' => 1,
                    'created_at' => date('c'),
                    'recipients' => ['user2@example.com'],
                ]
            ]))
        ]);
        $client = new Client(['handler' => $handler]);

        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setClient($client);

        $this->expectException(AssertionFailedError::class);

        $mailcatcher->seeEmailCount(3);
    }

    public function testLastMessageUsesIdAsTieBreakerForSameTimestamp()
    {
        $mailcatcher = $this->mailcatcherWithMessages(
            [$this->message(9), $this->message(10)],
            new Email(9, [], '', 'ninth'),
            new Email(10, [], '', 'tenth'),
        );

        $this->assertEquals(10, $mailcatcher->lastMessage()->getId());
    }

    public function testNthMessageCountsFromTheFirstReceivedEmail()
    {
        $mailcatcher = $this->mailcatcherWithMessages(
            [$this->message(3), $this->message(1), $this->message(2)],
            new Email(2, [], '', 'second'),
        );

        $this->assertEquals(2, $mailcatcher->nthMessage(2)->getId());
    }

    public function testNthMessageOrdersByReceivedTimeBeforeId()
    {
        $messages = [
            $this->message(1, createdAt: '2026-09-30T10:00:02+00:00'),
            $this->message(2, createdAt: '2026-09-30T10:00:01+00:00'),
            $this->message(3, createdAt: '2026-09-30T10:00:00+00:00'),
        ];
        $emails = [new Email(1, [], '', 'newest'), new Email(3, [], '', 'oldest')];

        $this->assertEquals(3, $this->mailcatcherWithMessages($messages, ...$emails)->nthMessage(1)->getId());
        $this->assertEquals(1, $this->mailcatcherWithMessages($messages, ...$emails)->nthMessage(3)->getId());
        $this->assertEquals(1, $this->mailcatcherWithMessages($messages, ...$emails)->lastMessage()->getId());
    }

    public function testNthMessageNoMessages()
    {
        $mailcatcher = $this->mailcatcherWithMessages([]);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No messages received');

        $mailcatcher->nthMessage(1);
    }

    public function testNthMessageBeyondReceivedEmails()
    {
        $mailcatcher = $this->mailcatcherWithMessages([$this->message(1)]);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No message found at position 2');

        $mailcatcher->nthMessage(2);
    }

    public function testNthMessageRejectsPositionBelowOne()
    {
        $mailcatcher = $this->mailcatcherWithMessages([$this->message(1)]);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Position must be 1 or greater, 0 given');

        $mailcatcher->nthMessage(0);
    }

    public function testNthMessageToCountsOnlyEmailsSentToAddress()
    {
        $mailcatcher = $this->mailcatcherWithMessages(
            [
                $this->message(1, recipients: ['<userA@example.com>', '<userA@example.com.test>']),
                $this->message(2, recipients: ['<userB@example.com>']),
                $this->message(3, recipients: ['<userA@example.com>']),
            ],
            new Email(3, [], '', 'second to userA'),
        );

        $this->assertEquals(3, $mailcatcher->nthMessageTo(2, 'userA@example.com')->getId());
    }

    public function testSeeInLastEmailSender()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, [], '', '', '<sender@example.com>'));

        $mailcatcher->seeInLastEmailSender('sender@example.com');
    }

    public function testSeeInLastEmailSenderFail()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, [], '', '', '<sender@example.com>'));

        $this->expectException(AssertionFailedError::class);

        $mailcatcher->seeInLastEmailSender('other@example.com');
    }

    public function testSeeInLastEmailRecipientMatchesAnyRecipient()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, ['<userA@example.com>', '<userB@example.com>'], '', ''));

        $mailcatcher->seeInLastEmailRecipient('userB@example.com');
    }

    public function testSeeInLastEmailRecipientDoesNotMatchAcrossRecipients()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, ['<alice@example.com>', '<bob@example.com>'], '', ''));

        $this->expectException(AssertionFailedError::class);

        $mailcatcher->seeInLastEmailRecipient('<alice@example.com>, <bob@example.com>');
    }

    public function testSeeInLastEmailRecipientFail()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, ['<userA@example.com>'], '', ''));

        $this->expectException(AssertionFailedError::class);

        $mailcatcher->seeInLastEmailRecipient('userB@example.com');
    }

    public function testGrabUrlsFromSinglePartEmailsRaisesNoDeprecation()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $sourceDirectory = dirname(__DIR__, 3) . '/src/';
        set_error_handler(static function (int $severity, string $message, string $file, int $line) use ($sourceDirectory): bool {
            if (!str_starts_with($file, $sourceDirectory)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            $mailcatcher->setLastMessage(new Email(1, [], '', $this->mimeSource('text/plain', 'Visit https://example.com/a')));
            $this->assertEquals(['https://example.com/a'], $mailcatcher->grabUrlsFromLastEmail());

            $mailcatcher->setLastMessage(new Email(1, [], '', $this->mimeSource('text/html', '<a href="https://example.com/b">B</a>')));
            $this->assertEquals(['https://example.com/b'], $mailcatcher->grabUrlsFromLastEmail());
        } finally {
            restore_error_handler();
        }
    }

    private function mailcatcherWithMessages(array $messages, Email ...$emails): MailCatcherTest_TestClass
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setClient(new Client(['handler' => new MockHandler([
            new Response(200, [], json_encode($messages)),
        ])]));
        $mailcatcher->setEmails(...$emails);

        return $mailcatcher;
    }

    private function message(
        int $id,
        string $sender = '<sender@example.com>',
        array $recipients = ['<user@example.com>'],
        string $createdAt = '2026-09-30T10:00:00+00:00'
    ): array {
        return [
            'id' => $id,
            'created_at' => $createdAt,
            'sender' => $sender,
            'recipients' => $recipients,
        ];
    }

    private function mimeSource(string $contentType, string $body): string
    {
        return "From: sender@example.com\r\n"
            . "To: user@example.com\r\n"
            . "Subject: Test\r\n"
            . "MIME-Version: 1.0\r\n"
            . "Content-Type: {$contentType}; charset=UTF-8\r\n"
            . "\r\n"
            . $body;
    }
}

class MailCatcherTest_TestClass extends MailCatcher
{
    private $lastMessage;
    private $lastMessageTo;
    private $lastMessageFrom;

    /**
     * @var array<int, Email>
     */
    private array $emails = [];

    public function __construct()
    {

    }

    public function setEmails(Email ...$emails)
    {
        foreach ($emails as $email) {
            $this->emails[$email->getId()] = $email;
        }
    }

    protected function emailFromId($id): Email
    {
        return $this->emails[$id] ?? parent::emailFromId($id);
    }

    public function getClient()
    {
        return $this->mailcatcher;
    }

    public function setClient(Client $client)
    {
        $this->mailcatcher = $client;
    }

    public function setLastMessage(Email $email)
    {
        $this->lastMessage = $email;
    }

    public function setLastMessageTo(Email $email)
    {
        $this->lastMessageTo = $email;
    }

    public function setLastMessageFrom(Email $email)
    {
        $this->lastMessageFrom = $email;
    }

    public function lastMessage(): \Codeception\Util\Email
    {
        if ($this->lastMessage !== null) {
            return $this->lastMessage;
        }

        return parent::lastMessage();
    }

    public function lastMessageTo(string $address): \Codeception\Util\Email
    {
        if ($this->lastMessageTo !== null) {
            return $this->lastMessageTo;
        }

        return parent::lastMessageTo($address);
    }

    public function lastMessageFrom(string $address): \Codeception\Util\Email
    {
        if ($this->lastMessageFrom !== null) {
            return $this->lastMessageFrom;
        }

        return parent::lastMessageFrom($address);
    }
}
