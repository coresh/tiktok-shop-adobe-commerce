<?php

namespace M2E\TikTokShop\Model\ControlPanel\Inspection\Result;

use Magento\Framework\ObjectManagerInterface;

class Factory
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param bool $status
     * @param string|null $errorMessage
     * @param \M2E\TikTokShop\Model\ControlPanel\Inspection\Issue[]|null $issues
     *
     * @return \M2E\TikTokShop\Model\ControlPanel\Inspection\Result
     */
    private function create($status, $errorMessage, $issues = [])
    {
        return $this->objectManager->create(
            \M2E\TikTokShop\Model\ControlPanel\Inspection\Result::class,
            [
                'status' => $status,
                'errorMessage' => $errorMessage,
                'issues' => $issues,
            ]
        );
    }

    /**
     * @param \M2E\TikTokShop\Model\ControlPanel\Inspection\Issue[] $issues
     *
     * @return \M2E\TikTokShop\Model\ControlPanel\Inspection\Result
     */
    public function createSuccess($issues)
    {
        return $this->create(true, null, $issues);
    }

    /**
     * @param string $errorMessage
     *
     * @return \M2E\TikTokShop\Model\ControlPanel\Inspection\Result
     */
    public function createFailed($errorMessage)
    {
        return $this->create(false, $errorMessage);
    }
}
