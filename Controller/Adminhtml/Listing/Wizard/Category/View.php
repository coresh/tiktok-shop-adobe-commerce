<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\Category;

use M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\CategorySelectMode;
use M2E\TikTokShop\Model\Listing\Wizard\StepDeclarationCollectionFactory;

class View extends \M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\StepAbstract
{
    use \M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\TikTokShop\Model\Category\Dictionary\Repository $dictionaryRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Dictionary\Repository $dictionaryRepository,
        \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\TikTokShop\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\TikTokShop\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage
    ) {
        parent::__construct($wizardManagerFactory, $uiListingRuntimeStorage, $uiWizardRuntimeStorage);

        $this->dictionaryRepository = $dictionaryRepository;
    }

    protected function getStepNick(): string
    {
        return StepDeclarationCollectionFactory::STEP_SELECT_CATEGORY;
    }

    protected function process(\M2E\TikTokShop\Model\Listing $listing)
    {
        $manager = $this->getWizardManager();
        $selectedMode = $manager->getStepData(StepDeclarationCollectionFactory::STEP_SELECT_CATEGORY_MODE);

        $mode = $selectedMode['mode'];

        if ($mode === CategorySelectMode::MODE_SAME) {
            return $this->stepSelectCategoryModeSame();
        }

        if ($mode === CategorySelectMode::MODE_MANUALLY) {
            return $this->stepSelectCategoryModeManually();
        }

        throw new \LogicException('Category mode unknown.');
    }

    private function stepSelectCategoryModeSame()
    {
        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\Category\Same::class,
            ),
        );

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('Set Category (All Products same Category)'));

        return $this->getResult();
    }

    private function stepSelectCategoryModeManually()
    {
        $manager = $this->getWizardManager();

        $wizardProducts = $manager->getProducts();

        $categoriesData = $this->getCategoriesData($wizardProducts);

        $block = $this
            ->getLayout()
            ->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\Category\Manually::class,
                '',
                [
                    'categoriesData' => $categoriesData,
                ],
            );

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->setAjaxContent($block->getChildBlock('grid')->toHtml());

            return $this->getResult();
        }

        $this->addContent($block);

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(
                 __('Set Category (Manually for each Product)'),
             );

        return $this->getResult();
    }

    /**
     * @param \M2E\TikTokShop\Model\Listing\Wizard\Product[] $wizardProduct
     *
     * @return array
     */
    private function getCategoriesData(array $wizardProduct): array
    {
        $result = [];
        foreach ($wizardProduct as $product) {
            $productData = [
                'product_id' => $product->getId(),
            ];

            $categoryDictionary = $product->getCategoryDictionary();
            if ($categoryDictionary !== null) {
                $productData['value'] = $categoryDictionary->getCategoryId();
                $productData['path'] = $categoryDictionary->getPath();
            }

            $result[$product->getMagentoProductId()] = $productData;
        }

        return $result;
    }
}
