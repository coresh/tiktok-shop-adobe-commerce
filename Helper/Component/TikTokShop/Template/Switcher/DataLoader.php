<?php

namespace M2E\TikTokShop\Helper\Component\TikTokShop\Template\Switcher;

class DataLoader
{
    private \Magento\Store\Model\StoreManagerInterface $storeManager;
    private \M2E\TikTokShop\Model\TikTokShop\Template\Manager $templateManager;
    private \M2E\TikTokShop\Model\TikTokShop\Template\ManagerFactory $templateManagerFactory;
    private \M2E\TikTokShop\Helper\Magento\Attribute $magentoAttributeHelper;
    private \M2E\TikTokShop\Helper\Magento\AttributeSet $magentoAttributeSetHelper;
    private \M2E\TikTokShop\Helper\Data\GlobalData $globalData;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \M2E\TikTokShop\Model\TikTokShop\Template\Manager $templateManager,
        \M2E\TikTokShop\Model\TikTokShop\Template\ManagerFactory $templateManagerFactory,
        \M2E\TikTokShop\Helper\Magento\Attribute $magentoAttributeHelper,
        \M2E\TikTokShop\Helper\Magento\AttributeSet $magentoAttributeSetHelper
    ) {
        $this->storeManager = $storeManager;
        $this->templateManager = $templateManager;
        $this->templateManagerFactory = $templateManagerFactory;
        $this->magentoAttributeHelper = $magentoAttributeHelper;
        $this->magentoAttributeSetHelper = $magentoAttributeSetHelper;
        $this->globalData = $globalData;
        $this->accountRepository = $accountRepository;
    }

    public function load($source, array $params = [])
    {
        $data = null;

        if ($source instanceof \M2E\TikTokShop\Helper\Data\Session) {
            $data = $this->getDataFromSession($source, $params);
        }
        if ($source instanceof \M2E\TikTokShop\Model\Listing) {
            $data = $this->getDataFromListing($source, $params);
        }
        if ($source instanceof \M2E\TikTokShop\Model\ResourceModel\Product\Collection) {
            $data = $this->getDataFromListingProducts($source, $params);
        }
        if ($this->isTemplateInstance($source)) {
            $data = $this->getDataFromTemplate($source, $params);
        }
        if ($source instanceof \Magento\Framework\App\RequestInterface) {
            $data = $this->getDataFromRequest($source, $params);
        }

        if ($data === null) {
            throw new \InvalidArgumentException('Data source is invalid.');
        }

        $account = null;
        if ($data['account_id']) {
            $account = $this->accountRepository->get((int)$data['account_id']);
        }

        $storeId = (int)$data['store_id'];

        $attributeSets = $data['attribute_sets'];
        $attributes = $this->magentoAttributeHelper->getAll();

        $displayUseDefaultOption = $data['display_use_default_option'];

        $global = $this->globalData;

        $global->setValue('tiktokshop_account', $account);
        $global->setValue('tiktokshop_store', $this->storeManager->getStore($storeId));
        $global->setValue('tiktokshop_attribute_sets', $attributeSets);
        $global->setValue('tiktokshop_attributes', $attributes);
        $global->setValue('tiktokshop_display_use_default_option', $displayUseDefaultOption);

        foreach ($data['templates'] as $nick => $templateData) {
            $template = $this->templateManager->setTemplate($nick)->getTemplateModel();

            if ($templateData['id']) {
                $template->load($templateData['id']);
            }

            $global->setValue("tiktokshop_template_{$nick}", $template);
            $global->setValue("tiktokshop_template_mode_{$nick}", $templateData['mode']);
            $global->setValue("tiktokshop_template_force_parent_{$nick}", $templateData['force_parent']);
        }
    }

    //########################################

    private function getDataFromSession(\M2E\TikTokShop\Helper\Data\Session $source, array $params = [])
    {
        if (!isset($params['session_key'])) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Session key is not defined.');
        }
        $sessionKey = $params['session_key'];
        $sessionData = $source->getValue($sessionKey);

        $accountId = $sessionData['account_id'] ?? null;
        $storeId = $sessionData['store_id'] ?? null;
        $attributeSets = $this->magentoAttributeSetHelper
            ->getAll(\M2E\TikTokShop\Helper\Magento\AbstractHelper::RETURN_TYPE_IDS);

        $templates = [];

        foreach ($this->templateManager->getAllTemplates() as $nick) {
            $templateId = $sessionData["template_id_$nick"] ?? null;
            $templateMode = isset($sessionData["template_id_$nick"]) ? $sessionData["template_mode_$nick"] : null;

            if (empty($templateMode)) {
                $templateMode = \M2E\TikTokShop\Model\TikTokShop\Template\Manager::MODE_CUSTOM;
            }

            $templates[$nick] = [
                'id' => $templateId,
                'mode' => $templateMode,
                'force_parent' => false,
            ];
        }

        return [
            'account_id' => $accountId,
            'store_id' => $storeId,
            'attribute_sets' => $attributeSets,
            'display_use_default_option' => false,
            'templates' => $templates,
        ];
    }

    private function getDataFromListing(\M2E\TikTokShop\Model\Listing $source, array $params = [])
    {
        $accountId = $source->getAccountId();
        $storeId = $source->getStoreId();
        $attributeSets = $this->magentoAttributeSetHelper
            ->getAll(\M2E\TikTokShop\Helper\Magento\AbstractHelper::RETURN_TYPE_IDS);

        $templates = [];

        foreach ($this->templateManager->getAllTemplates() as $nick) {
            $manager = $this->templateManagerFactory->create()
                                                    ->setTemplate($nick)
                                                    ->setOwnerObject($source);

            $templateId = $manager->getIdColumnValue();
            $templateMode = $manager->getModeValue();

            $templates[$nick] = [
                'id' => $templateId,
                'mode' => $templateMode,
                'force_parent' => false,
            ];
        }

        return [
            'account_id' => $accountId,
            'store_id' => $storeId,
            'attribute_sets' => $attributeSets,
            'display_use_default_option' => false,
            'templates' => $templates,
        ];
    }

    private function getDataFromListingProducts($source, array $params = [])
    {
        /** @var \M2E\TikTokShop\Model\Product $listingProductFirst */
        $listingProductFirst = $source->getFirstItem();

        $productIds = [];
        foreach ($source as $listingProduct) {
            $productIds[] = $listingProduct->getData('product_id');
        }

        $accountId = $listingProductFirst->getListing()->getAccountId();
        $storeId = $listingProductFirst->getListing()->getStoreId();
        $attributeSets = $this->magentoAttributeSetHelper
            ->getFromProducts($productIds, \M2E\TikTokShop\Helper\Magento\AbstractHelper::RETURN_TYPE_IDS);

        $templates = [];

        foreach ($this->templateManager->getAllTemplates() as $nick) {
            $templateId = null;
            $templateMode = null;
            $forceParent = false;

            if ($source->getSize() <= 200) {
                foreach ($source->getItems() as $listingProduct) {
                    $manager = $this->templateManagerFactory->create()
                                                            ->setTemplate($nick)
                                                            ->setOwnerObject($listingProduct);

                    $currentProductTemplateId = $manager->getIdColumnValue();
                    $currentProductTemplateMode = $manager->getModeValue();

                    if ($templateId === null && $templateMode === null) {
                        $templateId = $currentProductTemplateId;
                        $templateMode = $currentProductTemplateMode;
                        continue;
                    }

                    if ($templateId != $currentProductTemplateId || $templateMode != $currentProductTemplateMode) {
                        $templateId = null;
                        $templateMode = \M2E\TikTokShop\Model\TikTokShop\Template\Manager::MODE_PARENT;
                        $forceParent = true;
                        break;
                    }
                }
            } else {
                $forceParent = true;
            }

            if ($templateMode === null) {
                $templateMode = \M2E\TikTokShop\Model\TikTokShop\Template\Manager::MODE_PARENT;
            }

            $templates[$nick] = [
                'id' => $templateId,
                'mode' => $templateMode,
                'force_parent' => $forceParent,
            ];
        }

        return [
            'account_id' => $accountId,
            'store_id' => $storeId,
            'attribute_sets' => $attributeSets,
            'display_use_default_option' => true,
            'templates' => $templates,
        ];
    }

    private function getDataFromTemplate($source, array $params = [])
    {
        $attributeSets = $this->magentoAttributeSetHelper
            ->getAll(\M2E\TikTokShop\Helper\Magento\AbstractHelper::RETURN_TYPE_IDS);

        $nick = $this->getTemplateNick($source);

        return [
            'account_id' => null,
            'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            'attribute_sets' => $attributeSets,
            'display_use_default_option' => true,
            'templates' => [
                $nick => [
                    'id' => $source->getId(),
                    'mode' => \M2E\TikTokShop\Model\TikTokShop\Template\Manager::MODE_TEMPLATE,
                    'force_parent' => false,
                ],
            ],
        ];
    }

    private function getDataFromRequest(\Magento\Framework\App\RequestInterface $source, array $params = [])
    {
        $id = $source->getParam('id');
        $nick = $source->getParam('nick');
        $mode = $source->getParam('mode', \M2E\TikTokShop\Model\TikTokShop\Template\Manager::MODE_CUSTOM);

        $attributeSets = $source->getParam('attribute_sets', '');
        $attributeSets = array_filter(explode(',', $attributeSets));

        if (empty($attributeSets)) {
            $attributeSets = $this->magentoAttributeSetHelper
                ->getAll(\M2E\TikTokShop\Helper\Magento\AbstractHelper::RETURN_TYPE_IDS);
        }

        return [
            'account_id' => $source->getParam('account_id'),
            'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            'attribute_sets' => $attributeSets,
            'display_use_default_option' => (bool)$source->getParam('display_use_default_option'),
            'templates' => [
                $nick => [
                    'id' => $id,
                    'mode' => $mode,
                    'force_parent' => false,
                ],
            ],
        ];
    }

    // ----------------------------------------

    private function getTemplateNick($source): string
    {
        if ($source instanceof \M2E\TikTokShop\Model\Template\SellingFormat) {
            return \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SELLING_FORMAT;
        }

        if ($source instanceof \M2E\TikTokShop\Model\Template\Synchronization) {
            return \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SYNCHRONIZATION;
        }

        if ($source instanceof \M2E\TikTokShop\Model\Template\Description) {
            return \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_DESCRIPTION;
        }

        if ($source instanceof \M2E\TikTokShop\Model\Template\Compliance) {
            return \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_COMPLIANCE;
        }

        throw new \M2E\TikTokShop\Model\Exception\Logic('Invalid source ' . $source);
    }

    /**
     * @param $source
     *
     * @return bool
     */
    private function isTemplateInstance($source): bool
    {
        return $source instanceof \M2E\TikTokShop\Model\Template\SellingFormat
            || $source instanceof \M2E\TikTokShop\Model\Template\Description
            || $source instanceof \M2E\TikTokShop\Model\Template\Synchronization
            || $source instanceof \M2E\TikTokShop\Model\Template\Compliance;
    }

    /**
     * @param $source
     *
     * @return bool
     */
    private function isHorizontalTemplate($source): bool
    {
        if (
            $source instanceof \M2E\TikTokShop\Model\Template\SellingFormat ||
            $source instanceof \M2E\TikTokShop\Model\Template\Synchronization ||
            $source instanceof \M2E\TikTokShop\Model\Template\Description
        ) {
            return true;
        }

        return false;
    }
}
