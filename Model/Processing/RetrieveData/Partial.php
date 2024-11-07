<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Processing\RetrieveData;

class Partial
{
    private const MAX_PARTS_ON_RUN = 5;

    private \M2E\TikTokShop\Model\Processing\Repository $repository;
    private \M2E\TikTokShop\Model\Connector\Client\Single $client;
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;

    public function __construct(
        \M2E\TikTokShop\Model\Processing\Repository $repository,
        \M2E\TikTokShop\Model\Connector\Client\Single $client,
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper
    ) {
        $this->repository = $repository;
        $this->client = $client;
        $this->exceptionHelper = $exceptionHelper;
    }

    public function process(): void
    {
        $borderData = \M2E\TikTokShop\Helper\Date::createCurrentGmt()
                                                 ->modify('+ 5 minutes');

        foreach ($this->repository->findPartialForDownloadData($borderData) as $processing) {
            try {
                $this->processRecord($processing);
            } catch (\Throwable $e) {
                $this->exceptionHelper->process($e, ['processing_id' => $processing->getId()]);

                $processing->failDownload($this->getFailedMessage());

                $this->repository->save($processing);
            }
        }
    }

    private function processRecord(\M2E\TikTokShop\Model\Processing $processing): void
    {
        for ($i = 0; $i < self::MAX_PARTS_ON_RUN; $i++) {
            $currentPartNumber = $processing->getDataNextPart() ?? 1;

            $command = new \M2E\TikTokShop\Model\Processing\Connector\PartialGetResultCommand(
                $processing->getServerHash(),
                $currentPartNumber,
            );

            /** @var \M2E\TikTokShop\Model\Processing\Connector\ResultCollection $resultCollection */
            $resultCollection = $this->client->process($command);
            if ($resultCollection->has($processing->getServerHash())) {
                $result = $resultCollection->get($processing->getServerHash());
            } else {
                $result = \M2E\TikTokShop\Model\Processing\Connector\Result::createNotFound(
                    $processing->getServerHash(),
                );
            }

            if ($result->isStatusNotFound()) {
                $processing->failDownload($this->getFailedMessage());

                $this->repository->save($processing);

                break;
            }

            if (!$result->isStatusCompleted()) {
                break;
            }

            $data = $result->getData() ?? [];
            if (!empty($data)) {
                $processing->addPartialData($data, $currentPartNumber);
            }

            $nextPart = $result->getNextPart();
            if ($nextPart !== null && $nextPart > 0) {
                if ($nextPart === $currentPartNumber) {
                    ++$nextPart;
                }

                $processing->setDataNextPart($nextPart);

                $this->repository->save($processing);

                continue;
            }

            $processing->completeDownload($result->getMessages() ?? []);

            $this->repository->save($processing);

            break;
        }
    }

    private function getFailedMessage(): \M2E\TikTokShop\Model\Connector\Response\Message
    {
        $message = new \M2E\TikTokShop\Model\Connector\Response\Message();
        $message->initFromPreparedData(
            'Request wait timeout exceeded.',
            \M2E\TikTokShop\Model\Response\Message::TYPE_ERROR,
        );

        return $message;
    }
}
