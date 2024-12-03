<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Connector\Response;

class MessageCollection
{
    /** @var \M2E\TikTokShop\Model\Connector\Response\Message[] */
    private array $messages;

    /**
     * @param Message[] $messages
     */
    public function __construct(array $messages)
    {
        $this->messages = $messages;
    }

    public function hasErrors(): bool
    {
        return !empty($this->getErrors());
    }

    /**
     * @return Message[]
     */
    public function getErrors(): array
    {
        $messages = [];
        foreach ($this->messages as $message) {
            if ($message->isError()) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    public function hasWarnings(): bool
    {
        return !empty($this->getWarnings());
    }

    /**
     * @return Message[]
     */
    public function getWarnings(): array
    {
        $messages = [];
        foreach ($this->messages as $message) {
            if ($message->isWarning()) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    public function hasSystemErrors(): bool
    {
        return !empty($this->getSystemErrors());
    }

    /**
     * @return Message[]
     */
    public function getSystemErrors(): array
    {
        $messages = [];
        foreach ($this->getErrors() as $message) {
            if ($message->isSenderSystem()) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    public function getCombinedSystemErrorsString(): string
    {
        $messages = $this->getSystemErrors();

        return !empty($messages) ?
            implode(', ', array_map(static fn(Message $message) => $message->getText(), $messages)) :
            '';
    }

    public function getCombinedComponentErrorsString(): string
    {
        $messages = [];
        foreach ($this->getErrors() as $message) {
            if (!$message->isSenderComponent()) {
                continue;
            }
            $messages[] = $message->getText();
        }

        return !empty($messages) ? implode(', ', $messages) : '';
    }

    /**
     * @return \M2E\TikTokShop\Model\Connector\Response\Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
