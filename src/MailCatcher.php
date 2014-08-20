<?php

namespace Codeception\Module;

use Codeception\Module;

class MailCatcher extends Module
{
    /**
     * @var \Guzzle\Http\Client
     */
    protected $mailcatcher;


    /**
     * @var array
     */
    protected $config = array('url', 'port');

    /**
     * @var array
     */
    protected $requiredFields = array('url', 'port');

    public function _initialize()
    {
        $url = $this->config['url'] . ':' . $this->config['port'];
        $this->mailcatcher = new \Guzzle\Http\Client($url);
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
        $this->mailcatcher->delete('/messages')->send();
    }


    /**
     * See In Last Email
     *
     * Look for a string in the most recent email
     *
     * @return void
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    public function seeInLastEmail($expected)
    {
        $email = $this->lastMessage();
        $this->seeInEmail($email, $expected);
    }

    /**
     * See In Last Email To
     *
     * Look for a string in the most recent email sent to $address
     *
     * @return void
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    public function seeInLastEmailTo($address, $expected)
    {
        $email = $this->lastMessageFrom($address);
        $this->seeInEmail($email, $expected);

    }

    /**
     * Grab Matches From Last Email
     *
     * Look for a regex in the email source and return it's matches
     *
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
     * @return string
     * @author Stephan Hochhaus <stephan@yauh.de>
     **/
    public function grabFromLastEmailTo($address, $regex)
    {
        $matches = $this->grabMatchesFromLastEmailTo($address, $regex);
        return $matches[0];
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
        $response = $this->mailcatcher->get('/messages')->send();
        $messages = $response->json();
        if (empty($messages)) {
            $this->fail("No messages received");
        }

        return $messages;
    }

    /**
     * Last Message
     *
     * Get the most recent email
     *
     * @return obj
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    protected function lastMessage()
    {
        $messages = $this->messages();

        $last = array_shift($messages);

        return $this->emailFromId($last['id']);
    }

    /**
     * Last Message From
     *
     * Get the most recent email sent to $address
     *
     * @return obj
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    protected function lastMessageFrom($address)
    {
        $messages = $this->messages();
        foreach ($messages as $message) {
            foreach ($message['recipients'] as $recipient) {
                if (strpos($recipient, $address) !== false) {
                    return $this->emailFromId($message['id']);
                }
            }
        }
        $this->fail("No messages sent to {$address}");
    }

    /**
     * Email from ID
     *
     * Given a mailcatcher id, returns the email's object
     *
     * @return obj
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    protected function emailFromId($id)
    {
        $response = $this->mailcatcher->get("/messages/{$id}.json")->send();
        $message = $response->json();
		$message['source'] = quoted_printable_decode($message['source']);
		return $message;
    }

    /**
     * See In Email
     *
     * Look for a string in an email
     *
     * @return void
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    protected function seeInEmail($email, $expected)
    {
        $this->assertContains($expected, $email['source'], "Email Contains");
    }

    /**
     * Grab From Email
     *
     * Return the matches of a regex against the raw email
     *
     * @return void
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    protected function grabMatchesFromEmail($email, $regex)
    {
        preg_match($regex, $email['source'], $matches);
        $this->assertNotEmpty($matches, "No matches found for $regex");
        return $matches;
    }

}
