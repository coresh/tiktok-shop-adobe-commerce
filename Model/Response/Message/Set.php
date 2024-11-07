<?php

namespace M2E\TikTokShop\Model\Response\Message;

class Set
{
    /** @var \M2E\TikTokShop\Model\Response\Message[] $entities */
    private array $entities = [];

    /**
     * @param \M2E\TikTokShop\Model\Response\Message[] $messages
     */
    public function __construct(array $messages)
    {
        $this->entities = $messages;
    }

    public static function createFromResponse(array $responseData): self
    {
        $messages = [];
        foreach ($responseData as $messageData) {
            $message = self::getEntityModel();
            $message->initFromResponseData($messageData);

            $messages[] = $message;
        }

        return new static($messages);
    }

    protected static function getEntityModel(): \M2E\TikTokShop\Model\Response\Message
    {
        return new \M2E\TikTokShop\Model\Response\Message();
    }

    //########################################

    public function addEntity(\M2E\TikTokShop\Model\Response\Message $message): void
    {
        $this->entities[] = $message;
    }

    public function clearEntities()
    {
        $this->entities = [];
    }

    //########################################

    /**
     * @return \M2E\TikTokShop\Model\Response\Message[]
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    public function getEntitiesAsArrays(): array
    {
        $result = [];

        foreach ($this->getEntities() as $message) {
            $result[] = $message->asArray();
        }

        return $result;
    }

    //########################################

    /**
     * @return \M2E\TikTokShop\Model\Response\Message[]
     */
    public function getErrorEntities(): array
    {
        $messages = [];

        foreach ($this->getEntities() as $message) {
            $message->isError() && $messages[] = $message;
        }

        return $messages;
    }

    /**
     * @return \M2E\TikTokShop\Model\Response\Message[]
     */
    public function getWarningEntities(): array
    {
        $messages = [];

        foreach ($this->getEntities() as $message) {
            $message->isWarning() && $messages[] = $message;
        }

        return $messages;
    }

    /**
     * @return \M2E\TikTokShop\Model\Response\Message[]
     */
    public function getSuccessEntities(): array
    {
        $messages = [];

        foreach ($this->getEntities() as $message) {
            $message->isSuccess() && $messages[] = $message;
        }

        return $messages;
    }

    /**
     * @return \M2E\TikTokShop\Model\Response\Message[]
     */
    public function getNoticeEntities(): array
    {
        $messages = [];

        foreach ($this->getEntities() as $message) {
            $message->isNotice() && $messages[] = $message;
        }

        return $messages;
    }

    //########################################

    public function hasErrorEntities()
    {
        return !empty($this->getErrorEntities());
    }

    public function hasWarningEntities()
    {
        return !empty($this->getWarningEntities());
    }

    public function hasSuccessEntities()
    {
        return !empty($this->getSuccessEntities());
    }

    public function hasNoticeEntities()
    {
        return !empty($this->getNoticeEntities());
    }

    //########################################

    public function getCombinedErrorsString()
    {
        $messages = [];

        foreach ($this->getErrorEntities() as $message) {
            $messages[] = $message->getText();
        }

        return !empty($messages) ? implode(', ', $messages) : null;
    }
}
