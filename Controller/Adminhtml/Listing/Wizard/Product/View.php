<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\Product;

use M2E\TikTokShop\Model\Listing\Wizard\StepDeclarationCollectionFactory;

class View extends \M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\StepAbstract
{
    use \M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper;
    private \M2E\TikTokShop\Model\Magento\Product\RuleFactory $magentoProductRuleFactory;
    private \M2E\TikTokShop\Model\Listing\Wizard\Manager $manager;
    private \M2E\TikTokShop\Helper\Data\Session $sessionHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        \M2E\TikTokShop\Model\Magento\Product\RuleFactory $magentoProductRuleFactory,
        \M2E\TikTokShop\Helper\Data\Session $sessionHelper,
        \M2E\TikTokShop\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\TikTokShop\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage
    ) {
        parent::__construct($wizardManagerFactory, $uiListingRuntimeStorage, $uiWizardRuntimeStorage);

        $this->globalDataHelper = $globalDataHelper;
        $this->magentoProductRuleFactory = $magentoProductRuleFactory;
        $this->sessionHelper = $sessionHelper;
    }

    protected function getStepNick(): string
    {
        return StepDeclarationCollectionFactory::STEP_SELECT_PRODUCTS;
    }

    protected function process(\M2E\TikTokShop\Model\Listing $listing)
    {
        $this->manager = $this->getWizardManager();

        $data = $this->manager->getStepData(StepDeclarationCollectionFactory::STEP_SELECT_PRODUCT_SOURCE);

        $source = $data['source'];

        if ($source === \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\ProductSourceSelect::MODE_PRODUCT) {
            return $this->showGridByCatalog(
                $listing,
                $source,
            );
        }

        if ($source === \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\ProductSourceSelect::MODE_CATEGORY) {
            return $this->showGridByCategories(
                $listing,
                $source,
            );
        }

        throw new \LogicException('Unknown source type.');
    }

    private function showGridByCatalog(\M2E\TikTokShop\Model\Listing $listing, string $source)
    {
        $this->setRuleData('product_add_step_one', $listing);

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->setAjaxContent(
                $this->getLayout()
                     ->createBlock(
                         \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\Product\Add\Grid::class,
                     )
                     ->toHtml(),
            );

            return $this->getResult();
        }

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('Select Magento Products'));

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\Product\Add::class,
                '',
                [
                    'sourceMode' => $source,
                ],
            ),
        );

        return $this->getResult();
    }

    private function showGridByCategories(\M2E\TikTokShop\Model\Listing $listing, string $source)
    {
        $this->setRuleData('product_add_step_one', $listing);

        $data = $this->manager->getStepData($this->getStepNick());
        $selectedProductsIds = $data['products_ids'] ?? [];

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->getParam('current_category_id')) {
                $data['current_category_id'] = $this->getRequest()->getParam('current_category_id');

                $this->manager->setStepData($this->getStepNick(), $data);
            }

            /** @var \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\Product\Add\Category\Grid $grid */
            $grid = $this->getLayout()
                         ->createBlock(
                             \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\Product\Add\Category\Grid::class,
                         );

            $grid->setSelectedIds($selectedProductsIds);
            $grid->setCurrentCategoryId($data['current_category_id']);

            $this->setAjaxContent($grid->toHtml());

            return $this->getResult();
        }

        $this->setPageHelpLink('https://docs-m2.m2epro.com/add-products-to-tiktok-shop-listing');

        $this->getResultPage()
             ->getConfig()
             ->getTitle()->prepend(__('Select Magento Products'));

        /** @var \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\Product\Add $gridContainer */
        $gridContainer = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\Product\Add::class,
            '',
            [
                'sourceMode' => $source,
            ],
        );
        $this->addContent($gridContainer);

        /** @var \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\Category\Add\Tree $treeBlock */
        $treeBlock = $this->getLayout()
                          ->createBlock(
                              \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\Category\Add\Tree::class,
                          );

        if (empty($data['current_category_id'])) {
            $currentNode = $treeBlock->getRoot()->getChildren()->getIterator()->current();
            if (!$currentNode) {
                throw new \M2E\TikTokShop\Model\Exception('No Categories found');
            }

            $data['current_category_id'] = $currentNode->getId();
            $this->manager->setStepData($this->getStepNick(), $data);
        }

        $treeBlock->setGridId($gridContainer->getChildBlock('grid')->getId());
        $treeBlock->setSelectedIds($selectedProductsIds);
        $treeBlock->setCurrentNodeById($data['current_category_id']);

        $gridContainer->getChildBlock('grid')->setTreeBlock($treeBlock);
        $gridContainer->getChildBlock('grid')->setSelectedIds($selectedProductsIds);
        $gridContainer->getChildBlock('grid')->setCurrentCategoryId($data['current_category_id']);

        return $this->getResult();
    }

    private function setRuleData(string $prefix, \M2E\TikTokShop\Model\Listing $listing): void
    {
        $storeId = $listing->getStoreId();
        $prefix .= $listing->getId();

        $this->globalDataHelper->setValue(
            'rule_prefix',
            $prefix,
        ); // \M2E\TikTokShop\Block\Adminhtml\Magento\Product\Grid:361

        $ruleModel = $this->magentoProductRuleFactory
            ->create()
            ->setData(
                [
                    'prefix' => $prefix,
                    'store_id' => $storeId,
                ],
            );

        $ruleParam = $this->getRequest()->getPost('rule');
        if (!empty($ruleParam)) {
            $this->sessionHelper->setValue(
                $prefix,
                $ruleModel->getSerializedFromPost($this->getRequest()->getPostValue()),
            );
        } elseif ($ruleParam !== null) {
            $this->sessionHelper->setValue($prefix, []);
        }

        $sessionRuleData = $this->sessionHelper->getValue($prefix);
        if (!empty($sessionRuleData)) {
            $ruleModel->loadFromSerialized($sessionRuleData);
        }

        $this->globalDataHelper->setValue(
            'rule_model',
            $ruleModel,
        ); // \M2E\TikTokShop\Block\Adminhtml\Magento\Product\Grid:69
    }
}
