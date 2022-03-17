<?php

namespace Codeception\Util;

class Email
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string[]
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
     * @param string[] $recipients
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
     * @return string[]
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

    public function getSourceQuotedPrintableDecoded(): string
    {
        return quoted_printable_decode($this->source);
    }

    public static function createFromMailcatcherData(array $data): \Codeception\Util\Email
    {
        return new self($data['id'], $data['recipients'], $data['subject'], $data['source']);
    }
}