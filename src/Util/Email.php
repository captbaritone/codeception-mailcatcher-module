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
     * @param int $id
     * @param array $recipients
     * @param string $subject
     * @param string $source
     */
    public function __construct(int $id, array $recipients, string $subject, string $source)
    {
        $this->id = $id;
        $this->recipients = $recipients;
        $this->subject = $subject;
        $this->source = $source;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return mixed[]
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public static function createFromMailcatcherData($data): \Codeception\Util\Email
    {
        return new self($data['id'], $data['recipients'], $data['subject'], $data['source']);
    }
}