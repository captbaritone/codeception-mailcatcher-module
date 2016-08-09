<?php

namespace Codeception\Module;

use Codeception\Module;

class MailCatcher extends Module
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $mailcatcher;

    /**
     * @var array
     */
    protected $config = array('url', 'port', 'guzzleRequestOptions');

    /**
     * @var array
     */
    protected $requiredFields = array('url', 'port');

    public function _initialize()
    {
        $base_uri = trim($this->config['url'], '/') . ':' . $this->config['port'];
        $this->mailcatcher = new \GuzzleHttp\Client(['base_uri' => $base_uri]);

        if (isset($this->config['guzzleRequestOptions'])) {
            foreach ($this->config['guzzleRequestOptions'] as $option => $value) {
                $this->mailcatcher->setDefaultOption($option, $value);
            }
        }
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
     *
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
     * @return void
     **/
    public function dontSeeInLastEmailSubjectTo($address, $unexpected)
    {
        $email = $this->lastMessageFrom($address);
        $this->dontSeeInEmailSubject($email, $unexpected);
    }

    /**
     * Last Message
     *
     * Get the most recent email
     *
     * @return obj
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
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
     * Last Message From
     *
     * Get the most recent email sent to $address
     *
     * @return obj
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
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

      if (count($ids) > 0)
        return $this->emailFromId(max($ids));

      $this->fail("No messages sent to {$address}");
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

    /**
     * Test email count equals expected value
     *
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
        usort($messages, array($this, 'messageSortCompare'));
        return $messages;
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
        $response = $this->mailcatcher->get("/messages/{$id}.json");
        $message = json_decode($response->getBody(), true);
        $message['source'] = quoted_printable_decode($message['source']);
        return $message;
    }

    /**
     * See In Subject
     *
     * Look for a string in an email subject
     *
     * @return void
     * @author Antoine Augusti <antoine.augusti@gmail.com>
     **/
    protected function seeInEmailSubject($email, $expected)
    {
        $this->assertContains($expected, $email['subject'], "Email Subject Contains");
    }

    /**
     * Don't See In Subject
     *
     * Look for the absence of a string in an email subject
     *
     * @return void
     **/
    protected function dontSeeInEmailSubject($email, $unexpected)
    {
        $this->assertNotContains($unexpected, $email['subject'], "Email Subject Does Not Contain");
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
     * Don't See In Email
     *
     * Look for the absence of a string in an email
     *
     * @return void
     **/
    protected function dontSeeInEmail($email, $unexpected)
    {
        $this->assertNotContains($unexpected, $email['source'], "Email Does Not Contain");
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

    static function messageSortCompare($messageA, $messageB) {
        $sortKeyA = $messageA['created_at'] . $messageA['id'];
        $sortKeyB = $messageB['created_at'] . $messageB['id'];
        return ($sortKeyA > $sortKeyB) ? -1 : 1;
    }

}
