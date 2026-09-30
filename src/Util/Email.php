<?php

namespace Codeception\Util;

class Email
{
    /**
     * @param string[] $recipients
     */
    public function __construct(
        private readonly int $id,
        private readonly array $recipients,
        private readonly ?string $subject,
        private readonly string $source,
        private readonly string $sender = '',
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSender(): string
    {
        return $this->sender;
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
        return new self($data['id'], $data['recipients'], $data['subject'], $data['source'], $data['sender'] ?? '');
    }
}
