<?php

namespace Codeception\Util;

class EmailTest extends \Codeception\Test\Unit
{
    public function testGetters()
    {
        $email = new Email(1, ['some@email.com'], 'Some subject', 'Source body');

        $this->assertEquals(1, $email->getId());
        $this->assertEquals(['some@email.com'], $email->getRecipients());
        $this->assertEquals('Some subject', $email->getSubject());
        $this->assertEquals('Source body', $email->getSource());
    }

    public function testCreateFromMailcatcherData()
    {
        $email = Email::createFromMailcatcherData([
            'id' => 1,
            'recipients' => ['some@email.com'],
            'subject' => 'Some subject',
            'source' => 'Source body'
        ]);

        $this->assertEquals(1, $email->getId());
        $this->assertEquals(['some@email.com'], $email->getRecipients());
        $this->assertEquals('Some subject', $email->getSubject());
        $this->assertEquals('Source body', $email->getSource());
    }
}
