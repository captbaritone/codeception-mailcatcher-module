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
    public function lastMessageFrom()
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([
                [
                    'id' => 1,
                    'created_at' => date('c'),
                    'sender' => 'sender@example.com',
                    'recipients' => ['user@example.com'],
                ],
                [
                    'id' => 2,
                    'created_at' => date('c'),
                    'sender' => 'sender2@example.com',
                    'recipients' => ['user2@example.com'],
                ],
                [
                    'id' => 3,
                    'created_at' => date('c'),
                    'sender' => 'sender3@example.com',
                    'recipients' => ['user3@example.com'],
                ]
            ]))
        ]);
        $client = new Client(['handler' => $handler]);

        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setClient($client);

        $this->assertEquals(
            $mailcatcher->getLastMessageFrom('sender2@example.com'),
            2
        );
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

    public function testGrabUrlForLinkFromEncodedHtmlEmail()
    {
        $html = '<a href="https://example.com/first?one=1&amp;two=2"><strong>Café &amp; "tea"</strong></a>'
            . '<a href="https://example.com/second">Café &amp; "tea"</a>';
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, [], '',
            "MIME-Version: 1.0\r\nContent-Type: multipart/alternative; boundary=parts\r\n\r\n"
            . "--parts\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\nPlain text\r\n"
            . "--parts\r\nContent-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: quoted-printable\r\n\r\n"
            . quoted_printable_encode($html) . "\r\n--parts--\r\n"
        ));

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
}

class MailCatcherTest_TestClass extends MailCatcher
{
    private $lastMessage;
    private $lastMessageTo;
    private $lastMessageFrom;

    public function __construct()
    {

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
