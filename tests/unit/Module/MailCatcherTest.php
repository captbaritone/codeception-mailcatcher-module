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

    public function testLastMessageFromNoMessages()
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([]))
        ]);
        $client = new Client(['handler' => $handler]);

        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setClient($client);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No messages received');

        $mailcatcher->lastMessageFrom('user2@example.com');
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

    public function setClient($client)
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

    public function lastMessage()
    {
        if ($this->lastMessage !== null) {
            return $this->lastMessage;
        }

        return parent::lastMessage();
    }

    public function lastMessageTo($address)
    {
        if ($this->lastMessageTo !== null) {
            return $this->lastMessageTo;
        }

        return parent::lastMessageTo($address);
    }

    public function lastMessageFrom($address)
    {
        if ($this->lastMessageFrom !== null) {
            return $this->lastMessageFrom;
        }

        return parent::lastMessageFrom($address);
    }
}