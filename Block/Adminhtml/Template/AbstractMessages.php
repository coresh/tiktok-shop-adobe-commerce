<?php

namespace M2E\TikTokShop\Block\Adminhtml\Template;

abstract class AbstractMessages extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock
{
    abstract protected function getMessages();

    public function getMessagesHtml()
    {
        $messages = $this->getMessages();

        if (empty($messages)) {
            return '';
        }

        $messagesBlock = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Messages::class);

        $first = true;
        foreach ($messages as $messageType => $messageText) {
            $message = '';
            if ($first) {
                $first = false;
                $refreshText = __('Refresh');
                $message .= <<<HTML
<div style="display: inline-block; float: right;">
    <a href="javascript: void(0);" class="refresh-messages">[$refreshText]</a>
</div>
HTML;
            }
            $message .= $messageText;
            $messagesBlock->addWarning($message);
        }

        return $messagesBlock->toHtml();
    }
}
