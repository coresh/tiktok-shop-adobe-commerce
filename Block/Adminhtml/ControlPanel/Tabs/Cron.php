<?php

namespace M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs;

use M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm;

class Cron extends AbstractForm
{
    public array $tasks = [];
    private \M2E\TikTokShop\Model\Cron\TaskRepository $taskRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Cron\TaskRepository $taskRepository,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->taskRepository = $taskRepository;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelCron');
        $this->setTemplate('control_panel/tabs/cron.phtml');
    }

    protected function _beforeToHtml()
    {
        $tasks = [];
        foreach ($this->taskRepository->getRegisteredTasks() as $task) {
            $group = $this->taskRepository->getTaskGroup($task);
            $nick = $this->taskRepository->getNick($task);
            $titleParts = explode('/', $nick);

            if (reset($titleParts) === $group) {
                array_shift($titleParts);
            }

            $taskTitle = preg_replace_callback(
                '/_([a-z])/i',
                function ($matches) {
                    return ucfirst($matches[1]);
                },
                implode(' > ', array_map('ucfirst', $titleParts))
            );

            $tasks[ucfirst($group)][$task] = $taskTitle;
        }

        foreach ($tasks as $group => &$tasksByGroup) {
            asort($tasksByGroup);
        }

        unset($tasksByGroup);

        $this->tasks = $tasks;

        return parent::_beforeToHtml();
    }
}
