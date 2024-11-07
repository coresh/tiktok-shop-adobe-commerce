<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Processing\Connector;

class Result
{
    public const STATUS_NOT_FOUND = 'not_found';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';

    private string $hash;
    private string $status;
    private ?array $messages;
    private ?array $data;
    private ?int $nextPart;

    public function __construct(
        string $hash,
        string $status,
        ?array $messages,
        ?array $data,
        ?int $nextPart
    ) {
        $this->hash = $hash;
        $this->status = $status;
        $this->messages = $messages;
        $this->data = $data;
        $this->nextPart = $nextPart;

        if ($this->messages !== null) {
            $messagesRawData = $this->messages;
            $this->messages = [];
            foreach ($messagesRawData as $messageData) {
                $message = new \M2E\TikTokShop\Model\Connector\Response\Message();
                $message->initFromPreparedData(
                    $messageData['text'],
                    $messageData['type'],
                    $messageData['sender'],
                    $messageData['code'],
                );

                $this->messages[] = $message;
            }
        }
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function isStatusNotFound(): bool
    {
        return $this->status === self::STATUS_NOT_FOUND;
    }

    public function isStatusProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isStatusCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return \M2E\TikTokShop\Model\Connector\Response\Message[]|null
     */
    public function getMessages(): ?array
    {
        return $this->messages;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function hasNextPart(): bool
    {
        return $this->nextPart !== null;
    }

    public function getNextPart(): ?int
    {
        return $this->nextPart;
    }

    public static function createNotFound(string $hash): self
    {
        return new self($hash, self::STATUS_NOT_FOUND, null, null, null);
    }
}
