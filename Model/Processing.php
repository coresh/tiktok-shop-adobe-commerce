<?php

namespace M2E\TikTokShop\Model;

use M2E\TikTokShop\Model\ResourceModel\Processing as ProcessingResource;

class Processing extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public const TYPE_SINGLE = 1;
    public const TYPE_PARTIAL = 2;

    public const STAGE_WAIT_SERVER = 'wait';
    public const STAGE_DOWNLOAD = 'download';
    public const STAGE_WAIT_PROCESS = 'wait_process';

    /** @var \M2E\TikTokShop\Model\Processing\Repository */
    private Processing\Repository $repository;

    public function __construct(
        \M2E\TikTokShop\Model\Processing\Repository $repository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $registry);
        $this->repository = $repository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ProcessingResource::class);
    }

    public function create(
        int $type,
        string $serverHash,
        string $handleNick,
        array $params,
        \DateTime $expireDate
    ): self {
        $this->setType($type)
             ->setData(ProcessingResource::COLUMN_SERVER_HASH, $serverHash)
             ->setData(ProcessingResource::COLUMN_HANDLER_NICK, $handleNick)
             ->setData(ProcessingResource::COLUMN_PARAMS, json_encode($params, JSON_THROW_ON_ERROR))
             ->setData(ProcessingResource::COLUMN_EXPIRATION_DATE, $expireDate->format('Y-m-d H:i:s'))
             ->setStage(self::STAGE_WAIT_SERVER)
             ->setData(ProcessingResource::COLUMN_IS_COMPLETED, 0);

        return $this;
    }

    public function getId(): int
    {
        return (int)parent::getId();
    }

    public function isTypeSingle(): bool
    {
        return $this->getType() === self::TYPE_SINGLE;
    }

    public function isTypePartial(): bool
    {
        return $this->getType() === self::TYPE_PARTIAL;
    }

    private function setType(int $type): self
    {
        if (!in_array($type, [self::TYPE_SINGLE, self::TYPE_PARTIAL], true)) {
            throw new \M2E\TikTokShop\Model\Exception\Logic("Processing type '$type' is not valid.");
        }

        $this->setData(ProcessingResource::COLUMN_TYPE, $type);

        return $this;
    }

    public function getType(): int
    {
        return (int)$this->getData(ProcessingResource::COLUMN_TYPE);
    }

    private function setStage(string $stage): self
    {
        $this->setData(ProcessingResource::COLUMN_STAGE, $stage);

        return $this;
    }

    public function getServerHash(): string
    {
        return $this->getData(ProcessingResource::COLUMN_SERVER_HASH);
    }

    public function getHandleNick(): string
    {
        return $this->getData(ProcessingResource::COLUMN_HANDLER_NICK);
    }

    public function getParams(): array
    {
        $params = $this->getData(ProcessingResource::COLUMN_PARAMS);
        if (empty($params)) {
            return [];
        }

        return json_decode($params, true, 512, JSON_THROW_ON_ERROR);
    }

    // ----------------------------------------

    public function addPartialData(array $data, int $partNumber): self
    {
        if (!$this->isTypePartial()) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Unable add partial data for simple processing');
        }

        $this->repository->createPartialData($this, $partNumber, $data);

        return $this;
    }

    /**
     * @return \M2E\TikTokShop\Model\Processing\PartialData[]
     */
    public function getPartialData(): array
    {
        if (!$this->isTypePartial()) {
            return [];
        }

        return $this->repository->getPartialData($this);
    }

    private function clearDataNextPart(): self
    {
        $this->setData(ProcessingResource::COLUMN_DATA_NEXT_PART, null);

        return $this;
    }

    public function setDataNextPart(int $next): self
    {
        $this->setStage(self::STAGE_DOWNLOAD);

        $this->setData(ProcessingResource::COLUMN_DATA_NEXT_PART, $next);

        return $this;
    }

    public function getDataNextPart(): ?int
    {
        $next = $this->getData(ProcessingResource::COLUMN_DATA_NEXT_PART);

        return empty($next) ? null : (int)$next;
    }

    // ----------------------------------------

    /**
     * @param \M2E\Core\Model\Connector\Response\Message[] $messages
     */
    public function completeDownload(array $messages): void
    {
        $this->setResultMessages($messages)
             ->clearDataNextPart()
             ->setStage(self::STAGE_WAIT_PROCESS);
    }

    public function failDownload(\M2E\Core\Model\Connector\Response\Message $message): void
    {
        $this->setResultMessages([$message])
             ->clearDataNextPart()
             ->setStage(self::STAGE_WAIT_PROCESS);
    }

    public function setSimpleResultData(array $data): self
    {
        $this->setData(
            ProcessingResource::COLUMN_RESULT_DATA,
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    public function getSimpleResultData(): array
    {
        $data = $this->getData(ProcessingResource::COLUMN_RESULT_DATA);
        if (empty($data)) {
            return [];
        }

        return json_decode((string)$data, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param \M2E\Core\Model\Connector\Response\Message[] $messages
     *
     * @return self
     * @throws \JsonException
     */
    private function setResultMessages(array $messages): self
    {
        $messages = array_map(
            static function (\M2E\Core\Model\Connector\Response\Message $message) {
                return $message->asArray();
            },
            $messages,
        );

        $this->setData(
            ProcessingResource::COLUMN_RESULT_MESSAGES,
            json_encode($messages, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    /**
     * @return \M2E\TikTokShop\Model\Response\Message[]
     * @throws \JsonException
     */
    public function getResultMessages(): array
    {
        $messagesData = $this->getData(ProcessingResource::COLUMN_RESULT_MESSAGES);
        if (empty($messagesData)) {
            return [];
        }

        return array_map(
            static function (array $data) {
                $message = new \M2E\Core\Model\Connector\Response\Message();
                $message->initFromPreparedData($data['text'], $data['type'], $data['sender'], $data['code']);

                return $message;
            },
            json_decode($messagesData, true, 512, JSON_THROW_ON_ERROR)
        );
    }

    public function isCompleted(): bool
    {
        return (bool)((int)$this->getData(ProcessingResource::COLUMN_IS_COMPLETED));
    }
}
