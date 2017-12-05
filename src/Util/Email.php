<?php

namespace Codeception\Util;

class Email
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var array
     */
    private $recipients;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $source;
    
    /**
     * @var array
     */
    private $attachments;

    /**
     * @param int $id
     * @param array $recipients
     * @param string $subject
     * @param string $source
     * @param array $atachments
     */
    public function __construct($id, array $recipients, $subject, $source, $attachments)
    {
        $this->id = $id;
        $this->recipients = $recipients;
        $this->subject = $subject;
        $this->source = $source;
        $this->attachments = $attachments;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }
    
    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    public static function createFromMailcatcherData($data)
    {
        return new self($data['id'], $data['recipients'], $data['subject'], $data['source'], $data['attachments']);
    }
}
