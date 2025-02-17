<?php

namespace M2E\TikTokShop\Block\Adminhtml\HealthStatus\Tabs;

class Dashboard extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm
{
    private string $currentVersion;
    private ?string $latestPublicVersion = null;
    private bool $cronIsNotWorking = false;
    private \M2E\TikTokShop\Model\HealthStatus\Task\Result\Set $resultSet;
    private \M2E\TikTokShop\Model\Module $module;
    private \M2E\TikTokShop\Model\Cron\Manager $cronManager;
    private \M2E\TikTokShop\Model\Cron\Config $cronConfig;

    public function __construct(
        \M2E\TikTokShop\Model\Cron\Config $cronConfig,
        \M2E\TikTokShop\Model\Cron\Manager $cronManager,
        \M2E\TikTokShop\Model\HealthStatus\Task\Result\Set $resultSet,
        \M2E\TikTokShop\Model\Module $module,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->resultSet = $resultSet;
        $this->module = $module;
        $this->cronManager = $cronManager;
        $this->cronConfig = $cronConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $this->prepareInfo();
        $form = $this->_formFactory->create();

        // -- Dynamic FieldSets for Info
        // ---------------------------------------
        $createdFieldSets = [];
        foreach ($this->resultSet->getByKeys() as $resultItem) {
            if (in_array($resultItem->getFieldSetName(), $createdFieldSets)) {
                continue;
            }

            $fieldSet = $form->addFieldset(
                'fieldset_' . strtolower($resultItem->getFieldSetName()),
                [
                    'legend' => $resultItem->getFieldSetName(),
                    'collapsable' => false,
                ]
            );

            foreach ($this->resultSet->getByFieldSet($this->resultSet->getFieldSetKey($resultItem)) as $byFieldSet) {
                $fieldSet->addField(
                    strtolower($byFieldSet->getTaskHash()),
                    'note',
                    [
                        'label' => $byFieldSet->getFieldName(),
                        'text' => $byFieldSet->getTaskMessage(),
                    ]
                );
            }

            $createdFieldSets[] = $resultItem->getFieldSetName();
        }

        // ---------------------------------------

        $fieldSet = $form->addFieldset(
            'version_info',
            [
                'legend' => __('Version Info'),
                'collapsable' => true,
            ]
        );

        $fieldSet->addField(
            'current_version',
            'note',
            [
                'label' => __('Current Version'),
                'text' => $this->currentVersion,
            ]
        );

        if ($this->latestPublicVersion) {
            $releaseNotesText = __('[release notes]');
            $documentationArticleUrl = 'https://docs-m2.m2epro.com/m2e-tiktok-shop-release-notes-policy';
            $fieldSet->addField(
                'latest_public_version',
                'note',
                [
                    'label' => __('Latest Version'),
                    'text' => <<<HTML
{$this->latestPublicVersion}
<a href="$documentationArticleUrl" target="_blank">$releaseNotesText</a>
HTML
                    ,
                ]
            );
        }

        // ---------------------------------------

        $fieldSet = $form->addFieldset(
            'cron_info',
            [
                'legend' => __('Cron Info'),
                'collapsable' => true,
            ]
        );

        $fieldSet->addField(
            'current_status_type',
            'note',
            [
                'label' => __('Type'),
                'text' => ucwords(str_replace('_', ' ', $this->cronConfig->getActiveRunner())),
            ]
        );

        $cronLastRunTime = $this->cronManager->getCronLastRun();
        $cronLastRunTimeText = 'N/A';
        if ($cronLastRunTime !== null) {
            $this->cronIsNotWorking = $this->cronManager->isCronLastRunMoreThan(12 * 3600);
            $cronLastRunTimeText = $cronLastRunTime->format('Y-m-d H:i:s');
        }

        $fieldSet->addField(
            'current_status_last_run',
            'note',
            [
                'label' => __('Last Run'),
                'text' => "<span>$cronLastRunTimeText</span>" .
                    ($this->cronIsNotWorking ? ' (' . __('not working') . ')' : ''),
                'style' => $this->cronIsNotWorking ? 'color: red' : '',
            ]
        );

        // ---------------------------------------

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function prepareInfo(): void
    {
        $this->currentVersion = $this->module->getPublicVersion();
        if ($this->module->hasLatestVersion()) {
            $this->latestPublicVersion = $this->module->getLatestVersion();
        }
    }
}
