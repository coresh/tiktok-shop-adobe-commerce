<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Settings\LogsClearing;

class Save extends \M2E\TikTokShop\Controller\Adminhtml\AbstractBase
{
    private \M2E\TikTokShop\Model\Log\Clearing $clearing;

    public function __construct(\M2E\TikTokShop\Model\Log\Clearing $clearing)
    {
        parent::__construct();

        $this->clearing = $clearing;
    }

    public function execute()
    {
        $task = $this->getRequest()->getParam('task');
        $log = $this->getRequest()->getParam('log');

        $messages = [];
        if ($task !== null) {
            $title = ucwords(str_replace('_', ' ', $log));

            switch ($task) {
                case 'run_now':
                    $this->clearing->clearOldRecords($log);
                    $tempString = (string)__(
                        'Log for %title has been cleared.',
                        ['title' => $title],
                    );
                    $messages[] = ['success' => $tempString];
                    break;

                case 'clear_all':
                    $this->clearing->clearAllLog($log);
                    $tempString = (string)__(
                        'All Log for %title has been cleared.',
                        ['title' => $title],
                    );
                    $messages[] = ['success' => $tempString];
                    break;
            }
        }

        $this->setJsonContent(
            ['success' => true, 'messages' => $messages]
        );

        return $this->getResult();
    }
}
