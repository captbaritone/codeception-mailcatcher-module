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
        $mailcatcher->setLastMessage(new Email(1, '', [], '', 'Test body and some more text'));

        $mailcatcher->seeInLastEmail('Test body');
    }

    public function testDontSeeInLastEmail()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, '', [], '', 'Body with test data'));

        $mailcatcher->dontSeeInLastEmail('Test body');
    }

    public function testSeeInLastEmailSubject()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, '', [], 'Test subject', ''));

        $mailcatcher->seeInLastEmailSubject('Test subject');
    }

    public function testDontSeeInLastEmailSubject()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, '', [], 'Test subject', ''));

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
                    'subject' => '',
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
        $recipients = ['user2@example.com'];
        $interestingMessage = [
            'id' => 2,
            'created_at' => date('c'),
            'sender' => 'sender2@example.com',
            'recipients' => $recipients,
            'subject' => '',
        ];
        // Queue all responses here,
        // see https://guzzler.dev/troubleshooting/#mock-queue-is-empty
        $handler = new MockHandler([
            new Response(200, [], json_encode([
                [
                    'id' => 1,
                    'created_at' => date('c'),
                    'sender' => 'sender@example.com',
                    'recipients' => ['user@example.com'],
                    'subject' => '',
                ],
                $interestingMessage,
                [
                    'id' => 3,
                    'created_at' => date('c'),
                    'sender' => 'sender3@example.com',
                    'recipients' => ['user3@example.com'],
                    'subject' => '',
                ]
            ])),
            // \Codeception\Module\MailCatcher::emailFromId
            // $this->mailcatcher->get("/messages/{$id}.json");
            new Response(200, [], json_encode($interestingMessage)),
            // \Codeception\Module\MailCatcher::emailFromId
            // $plainMessage = $this->mailcatcher->get("/messages/{$id}.source");
            new Response(200, [], json_encode($interestingMessage)),
        ]);
        $client = new Client(['handler' => $handler]);

        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setClient($client);

        $this->assertEquals(
            $recipients,
            $mailcatcher->lastMessageFrom('sender2@example.com')->getRecipients(),
        );
    }

    /**
     * Check that we get the correct nth Message from sender
     *
     * @return void
     */
    public function testNthMessageFrom()
    {
        $recipients = ['user2@example.com', 'user4@example.com'];
        $interestingMessage = [
            'id' => 2,
            'created_at' => date('c'),
            'sender' => 'sender@example.com',
            'recipients' => $recipients,
            'subject' => '',
        ];
        // Queue all responses here,
        // see https://guzzler.dev/troubleshooting/#mock-queue-is-empty
        $handler = new MockHandler([
            new Response(200, [], json_encode([
                [
                    'id' => 1,
                    'created_at' => date('c'),
                    'sender' => 'sender@example.com',
                    'recipients' => ['user@example.com'],
                    'subject' => '',
                ],
                $interestingMessage,
                [
                    'id' => 3,
                    'created_at' => date('c'),
                    'sender' => 'sender3@example.com',
                    'recipients' => ['user3@example.com'],
                    'subject' => '',
                ]
            ])),
            // \Codeception\Module\MailCatcher::emailFromId
            // $this->mailcatcher->get("/messages/{$id}.json");
            new Response(200, [], json_encode($interestingMessage)),
            // \Codeception\Module\MailCatcher::emailFromId
            // $plainMessage = $this->mailcatcher->get("/messages/{$id}.source");
            new Response(200, [], json_encode($interestingMessage)),
        ]);
        $client = new Client(['handler' => $handler]);

        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setClient($client);

        $this->assertEquals(
            $recipients,
            $mailcatcher->nthMessageFrom(2, 'sender@example.com')->getRecipients(),
        );
    }

    public function testSeeInLastEmailSender()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, 'sender@example.com', ['test@example.com'], '', 'Test body and some more text'));

        $mailcatcher->seeInLastEmailSender('sender@example.com');
    }
    
    public function testSeeInNthEmailSender()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setMessages([
            new Email(1, 'senderA@example.com', [], '', 'Test body and some more text'),
            new Email(2, 'senderB@example.com', [], '', 'Morbi eget venenatis massa'),
            new Email(3, 'senderC@example.com', [], '', 'Nunc dignissim sapien pulvinar mauris ultrices'),
        ]);

        $mailcatcher->seeInNthEmailSender(2, 'senderB@example.com');
    }
    
    public function testSeeInLastEmailRecipient()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessage(new Email(1, '', ['user@example.com'], '', 'Test body and some more text'));

        $mailcatcher->seeInLastEmailRecipient('user@example.com');
    }

    public function testSeeInNthEmailRecipient()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setMessages([
            new Email(1, 'senderA@example.com', ['user@example.com', 'userB@example.com'], '', 'Test body and some more text'),
            new Email(2, 'senderB@example.com', ['userB@example.com'], '', 'Morbi eget venenatis massa'),
            new Email(3, 'senderC@example.com', ['userC@example.com'], '', 'Nunc dignissim sapien pulvinar mauris ultrices'),
        ]);

        $mailcatcher->seeInNthEmailRecipient(2, 'userB@example.com');
    }

    public function testSeeInLastEmailTo()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessageTo(new Email(1, '', ['test@example.com'], '', 'Test body and some more text'));

        $mailcatcher->seeInLastEmailTo('test@example.com', 'Test body');
    }

    public function testDontSeeInLastEmailTo()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessageTo(new Email(1, '', ['test@example.com'], '', 'Body with test data'));

        $mailcatcher->dontSeeInLastEmailTo('test@example.com', 'Test body');
    }

    public function testSeeInLastEmailSubjectTo()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessageTo(new Email(1, '', ['test@example.com'], 'Test subject', ''));

        $mailcatcher->seeInLastEmailSubjectTo('test@example.com', 'Test subject');
    }

    public function testDontSeeInLastEmailSubjectTo()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setLastMessageTo(new Email(1, '', ['test@example.com'], 'Test subject', ''));

        $mailcatcher->dontSeeInLastEmailSubjectTo('test@example.com', 'Hello world');
    }
    
    public function testNthMessageNoMessages()
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([]))
        ]);
        $client = new Client(['handler' => $handler]);

        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setClient($client);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No messages received');

        $mailcatcher->nthMessage(1);
    }
    
    public function testSeeInNthEmail()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setMessages([
            new Email(1, '', [], '', 'Test body and some more text'),
            new Email(2, '', [], '', 'Morbi eget venenatis massa'),
            new Email(3, '', [], '', 'Nunc dignissim sapien pulvinar mauris ultrices'),
        ]);

        $mailcatcher->seeInNthEmail(1, 'Test body');
        $mailcatcher->seeInNthEmail(2, 'Morbi eget venenatis massa');
        $mailcatcher->seeInNthEmail(3, 'Nunc dignissim');
    }

    public function testDontSeeInNthEmail()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setMessages([
            new Email(1, '', [], '', 'Test body and some more text'),
            new Email(2, '', [], '', 'Morbi eget venenatis massa'),
            new Email(2, '', [], '', 'Nunc dignissim sapien pulvinar mauris ultrices'),
        ]);

        $mailcatcher->dontSeeInNthEmail(3, 'Test body');
    }

    public function testSeeInNthEmailSubject()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setMessages([
            new Email(1, '', [], 'Test subject', ''),
            new Email(2, '', [], 'Aliquam eget', ''),
            new Email(2, '', [], 'Vivamus maximus', ''),
        ]);

        $mailcatcher->seeInNthEmailSubject(1, 'Test subject');
        $mailcatcher->seeInNthEmailSubject(2, 'Aliquam eget');
        $mailcatcher->seeInNthEmailSubject(3, 'maximus');
    }

    public function testDontSeeInNthEmailSubject()
    {
        $mailcatcher = new MailCatcherTest_TestClass();
        $mailcatcher->setMessages([
            new Email(1, '', [], 'Test subject', ''),
            new Email(2, '', [], 'Aliquam eget', ''),
            new Email(2, '', [], 'Vivamus maximus', ''),
        ]);

        $mailcatcher->dontSeeInNthEmailSubject(2, 'Vivamus');
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
    /**
     * @var array $messages
     */
    private $messages = [];
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

    /**
     * @return Email[]
     */
    protected function messages(): array
    {
        if (!empty($this->messages)) {
            return $this->messages;
        }
        
        return parent::messages();
    }

    /**
     * @param Email[] $emails
     */
    public function setMessages(array $emails)
    {
        $this->messages = $emails;
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

    public function nthMessage(int $nth): \Codeception\Util\Email
    {
        if ($nth < 1) {
            $this->fail("nth must be greater than zero");
        }

        $messages = $this->messages();
        if (empty($messages)) {
            $this->fail("No messages received");
        }

        if (!isset($messages[$nth - 1])) {
            $this->fail("No message found at location {$nth}");
        }

        return $messages[$nth - 1];
    }
}
