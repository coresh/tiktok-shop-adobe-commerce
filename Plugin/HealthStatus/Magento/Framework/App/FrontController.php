<?php

namespace M2E\TikTokShop\Plugin\HealthStatus\Magento\Framework\App;

use Magento\Framework\Message\MessageInterface;
use M2E\TikTokShop\Model\HealthStatus\Task\Result;

class FrontController extends \M2E\TikTokShop\Plugin\AbstractPlugin
{
    public const MESSAGE_IDENTIFIER = 'tiktokshop_health_status_front_controller_message';

    /** @var \Magento\Framework\Message\ManagerInterface */
    private $messageManager;
    private \M2E\TikTokShop\Model\HealthStatus\CurrentStatus $healthStatusCurrentStatus;
    private \M2E\TikTokShop\Model\HealthStatus\Notification\MessageBuilder $notificationMessageBuilder;
    private \M2E\TikTokShop\Model\HealthStatus\Notification\Settings $notificationSettings;

    public function __construct(
        \M2E\TikTokShop\Helper\Factory $helperFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \M2E\TikTokShop\Model\HealthStatus\CurrentStatus $healthStatusCurrentStatus,
        \M2E\TikTokShop\Model\HealthStatus\Notification\MessageBuilder $notificationMessageBuilder,
        \M2E\TikTokShop\Model\HealthStatus\Notification\Settings $notificationSettings
    ) {
        $this->messageManager = $messageManager;
        $this->healthStatusCurrentStatus = $healthStatusCurrentStatus;
        $this->notificationMessageBuilder = $notificationMessageBuilder;
        $this->notificationSettings = $notificationSettings;
        parent::__construct($helperFactory);
    }

    //########################################

    public function aroundDispatch($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('dispatch', $interceptor, $callback, $arguments);
    }

    protected function processDispatch($interceptor, \Closure $callback, $arguments)
    {
        $request = isset($arguments[0]) ? $arguments[0] : null;

        if (!($request instanceof \Magento\Framework\App\Request\Http)) {
            return $callback(...$arguments);
        }

        if ($this->shouldBeAdded($request)) {
            switch ($this->healthStatusCurrentStatus->get()) {
                case Result::STATE_NOTICE:
                    $messageType = MessageInterface::TYPE_NOTICE;
                    break;

                case Result::STATE_WARNING:
                    $messageType = MessageInterface::TYPE_WARNING;
                    break;

                default:
                case Result::STATE_CRITICAL:
                    $messageType = MessageInterface::TYPE_ERROR;
                    break;
            }

            $this->messageManager->addMessage(
                $this->messageManager->createMessage($messageType, self::MESSAGE_IDENTIFIER)
                                     ->setText($this->notificationMessageBuilder->build())
            );
        }

        return $callback(...$arguments);
    }

    private function shouldBeAdded(\Magento\Framework\App\RequestInterface $request)
    {
        /** @var \Magento\Framework\App\Request\Http $request */

        if ($request->isPost() || $request->isAjax()) {
            return false;
        }

        // do not show on own page
        if (strpos($request->getPathInfo(), 'healthStatus') !== false) {
            return false;
        }

        if (!$this->notificationSettings->isModeMagentoPages()) {
            return false;
        }

        if ($this->healthStatusCurrentStatus->get() < $this->notificationSettings->getLevel()) {
            return false;
        }

        // after redirect message can be added twice
        foreach ($this->messageManager->getMessages()->getItems() as $message) {
            if ($message->getIdentifier() == self::MESSAGE_IDENTIFIER) {
                return false;
            }
        }

        return true;
    }
}
