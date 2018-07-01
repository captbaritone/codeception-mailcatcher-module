<?php

namespace Codeception\Module;

use Codeception\Module;
use Codeception\Util\Email;
use GuzzleHttp\Client;

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

    public function _initialize()
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
     * @return void
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    public function resetEmails()
    {
        $this->mailcatcher->delete('/messages');
    }


    /**
     * See In Last Email
     *
     * Look for a string in the most recent email
     *
     * @param string $expected
     * @return void
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    public function seeInLastEmail($expected)
    {
        $email = $this->lastMessage();
        $this->seeInEmail($email, $expected);
    }

    /**
     * See In Last Email subject
     *
     * Look for a string in the most recent email subject
     *
     * @param string $expected
     * @return void
     * @author Antoine Augusti <antoine.augusti@gmail.com>
     **/
    public function seeInLastEmailSubject($expected)
    {
        $email = $this->lastMessage();
        $this->seeInEmailSubject($email, $expected);
    }

    /**
     * Don't See In Last Email subject
     *
     * Look for the absence of a string in the most recent email subject
     *
     * @param string $expected
     * @return void
     **/
    public function dontSeeInLastEmailSubject($expected)
    {
        $email = $this->lastMessage();
        $this->dontSeeInEmailSubject($email, $expected);
    }

    /**
     * Don't See In Last Email
     *
     * Look for the absence of a string in the most recent email
     *
     * @param string $unexpected
     * @return void
     **/
    public function dontSeeInLastEmail($unexpected)
    {
        $email = $this->lastMessage();
        $this->dontSeeInEmail($email, $unexpected);
    }

    /**
     * See In Last Email To
     *
     * Look for a string in the most recent email sent to $address
     *
     * @param string $address
     * @param string $expected
     * @return void
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    public function seeInLastEmailTo($address, $expected)
    {
        $email = $this->lastMessageFrom($address);
        $this->seeInEmail($email, $expected);
    }

    /**
     * Don't See In Last Email To
     *
     * Look for the absence of a string in the most recent email sent to $address
     * @param string $address
     * @param string $unexpected
     * @return void
     **/
    public function dontSeeInLastEmailTo($address, $unexpected)
    {
        $email = $this->lastMessageFrom($address);
        $this->dontSeeInEmail($email, $unexpected);
    }

    /**
     * See In Last Email Subject To
     *
     * Look for a string in the most recent email subject sent to $address
     *
     * @param string $address
     * @param string $expected
     * @return void
     * @author Antoine Augusti <antoine.augusti@gmail.com>
     **/
    public function seeInLastEmailSubjectTo($address, $expected)
    {
        $email = $this->lastMessageFrom($address);
        $this->seeInEmailSubject($email, $expected);
    }

    /**
     * Don't See In Last Email Subject To
     *
     * Look for the absence of a string in the most recent email subject sent to $address
     *
     * @param string $address
     * @param string $unexpected
     * @return void
     **/
    public function dontSeeInLastEmailSubjectTo($address, $unexpected)
    {
        $email = $this->lastMessageFrom($address);
        $this->dontSeeInEmailSubject($email, $unexpected);
    }

    /**
     * @return Email
     */
    public function lastMessage()
    {
        $messages = $this->messages();
        if (empty($messages)) {
            $this->fail("No messages received");
        }

        $last = array_shift($messages);

        return $this->emailFromId($last['id']);
    }

    /**
     * @param $address
     * @return Email
     */
    public function lastMessageFrom($address)
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

    /**
     * Grab Matches From Last Email
     *
     * Look for a regex in the email source and return it's matches
     *
     * @param string $regex
     * @return array
     * @author Stephan Hochhaus <stephan@yauh.de>
     **/
    public function grabMatchesFromLastEmail($regex)
    {
        $email = $this->lastMessage();
        $matches = $this->grabMatchesFromEmail($email, $regex);
        return $matches;
    }

    /**
     * Grab From Last Email
     *
     * Look for a regex in the email source and return it
     *
     * @param string $regex
     * @return string
     * @author Stephan Hochhaus <stephan@yauh.de>
     **/
    public function grabFromLastEmail($regex)
    {
        $matches = $this->grabMatchesFromLastEmail($regex);
        return $matches[0];
    }

    /**
     * Grab Matches From Last Email To
     *
     * Look for a regex in most recent email sent to $addres email source and
     * return it's matches
     *
     * @param string $address
     * @param string $regex
     * @return array
     * @author Stephan Hochhaus <stephan@yauh.de>
     **/
    public function grabMatchesFromLastEmailTo($address, $regex)
    {
        $email = $this->lastMessageFrom($address);
        $matches = $this->grabMatchesFromEmail($email, $regex);
        return $matches;
    }

    /**
     * Grab From Last Email To
     *
     * Look for a regex in most recent email sent to $addres email source and
     * return it
     *
     * @param string $address
     * @param string $regex
     * @return string
     * @author Stephan Hochhaus <stephan@yauh.de>
     **/
    public function grabFromLastEmailTo($address, $regex)
    {
        $matches = $this->grabMatchesFromLastEmailTo($address, $regex);
        return $matches[0];
    }

    /**
     * Grab Urls From Email
     *
     * Return the urls the email conteins
     *
     * @return array
     * @author Marcelo Briones <ing@marcelobriones.com.ar>
     */
    public function grabUrlsFromLastEmail()
    {
        if (!class_exists('\\PhpMimeMailParser\\Parser')) {
            throw new \Exception("'php-mime-mail-parser/php-mime-mail-parser' required for 'grabUrlsFromLastEmail' method.");
        }

        $email = $this->lastMessage();

        $parser = new \PhpMimeMailParser\Parser();
        $parser->setText($email->getSource());

        $text = $parser->getMessageBody('text');
        preg_match_all('#\bhttp?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $text, $text_matches);

        $html = $parser->getMessageBody('html');
        preg_match_all('#\bhttp?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $html, $html_matches);

        return array_merge($text_matches[0], $html_matches[0]);
    }

    /**
     * Test email count equals expected value
     *
     * @param int $expected
     * @return void
     * @author Mike Crowe <drmikecrowe@gmail.com>
     **/
    public function seeEmailCount($expected)
    {
        $messages = $this->messages();
        $count = count($messages);
        $this->assertEquals($expected, $count);
    }

    // ----------- HELPER METHODS BELOW HERE -----------------------//

    /**
     * Messages
     *
     * Get an array of all the message objects
     *
     * @return array
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    protected function messages()
    {
        $response = $this->mailcatcher->get('/messages');
        $messages = json_decode($response->getBody(), true);
        // Ensure messages are shown in the order they were recieved
        // https://github.com/sj26/mailcatcher/pull/184
        usort($messages, function ($messageA, $messageB) {
            $sortKeyA = $messageA['created_at'] . $messageA['id'];
            $sortKeyB = $messageB['created_at'] . $messageB['id'];
            return ($sortKeyA > $sortKeyB) ? -1 : 1;
        });
        return $messages;
    }

    /**
     * @param $id
     * @return Email
     */
    protected function emailFromId($id)
    {
        $response = $this->mailcatcher->get("/messages/{$id}.json");
        $messageData = json_decode($response->getBody(), true);
        $messageData['source'] = quoted_printable_decode($messageData['source']);

        return Email::createFromMailcatcherData($messageData);
    }

    /**
     * @param Email $email
     * @param $expected
     */
    protected function seeInEmailSubject(Email $email, $expected)
    {
        $this->assertContains($expected, $email->getSubject(), "Email Subject Contains");
    }

    /**
     * @param Email $email
     * @param $unexpected
     */
    protected function dontSeeInEmailSubject(Email $email, $unexpected)
    {
        $this->assertNotContains($unexpected, $email->getSubject(), "Email Subject Does Not Contain");
    }

    /**
     * @param Email $email
     * @param $expected
     */
    protected function seeInEmail(Email $email, $expected)
    {
        $this->assertContains($expected, $email->getSource(), "Email Contains");
    }

    /**
     * @param Email $email
     * @param $unexpected
     */
    protected function dontSeeInEmail(Email $email, $unexpected)
    {
        $this->assertNotContains($unexpected, $email->getSource(), "Email Does Not Contain");
    }

    /**
     * @param Email $email
     * @param $regex
     * @return array
     */
    protected function grabMatchesFromEmail(Email $email, $regex)
    {
        preg_match($regex, $email->getSource(), $matches);
        $this->assertNotEmpty($matches, "No matches found for $regex");
        return $matches;
    }
}
