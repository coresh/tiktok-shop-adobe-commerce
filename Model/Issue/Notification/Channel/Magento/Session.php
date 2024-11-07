<?php

namespace M2E\TikTokShop\Model\Issue\Notification\Channel\Magento;

use M2E\TikTokShop\Controller\Adminhtml\AbstractBase;
use M2E\TikTokShop\Model\Exception\Logic;
use M2E\TikTokShop\Model\Issue\DataObject;
use M2E\TikTokShop\Model\Issue\Notification\ChannelInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface as Message;

class Session implements ChannelInterface
{
    /** @var ManagerInterface */
    private $messageManager;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(ManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    /**
     * @inheritDoc
     * @throws Logic
     */
    public function addMessage(DataObject $message): void
    {
        switch ($message->getType()) {
            case Message::TYPE_NOTICE:
                $this->messageManager->addNotice(
                    $message->getText(),
                    AbstractBase::GLOBAL_MESSAGES_GROUP
                );
                break;

            case Message::TYPE_SUCCESS:
                $this->messageManager->addSuccess(
                    $message->getText(),
                    AbstractBase::GLOBAL_MESSAGES_GROUP
                );
                break;

            case Message::TYPE_WARNING:
                $this->messageManager->addWarning(
                    $message->getText(),
                    AbstractBase::GLOBAL_MESSAGES_GROUP
                );
                break;

            case Message::TYPE_ERROR:
                $this->messageManager->addError(
                    $message->getText(),
                    AbstractBase::GLOBAL_MESSAGES_GROUP
                );
                break;

            default:
                throw new Logic(
                    sprintf('Unsupported message type [%s]', $message->getType())
                );
        }
    }
}
