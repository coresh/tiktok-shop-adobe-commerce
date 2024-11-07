<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Product\Grid\AllItems;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;

class ActionFilter extends \Magento\Ui\Component\MassAction\Filter
{
    /** @var \Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface */
    private $dataProvider;

    public function getCollection(AbstractDb $collection)
    {
        $selected = $this->request->getParam(static::SELECTED_PARAM);
        $excluded = $this->request->getParam(static::EXCLUDED_PARAM);

        $isExcludedIdsValid = (is_array($excluded) && !empty($excluded));
        $isSelectedIdsValid = (is_array($selected) && !empty($selected));

        if ('false' !== $excluded) {
            if (!$isExcludedIdsValid && !$isSelectedIdsValid) {
                throw new LocalizedException(__('An item needs to be selected. Select and try again.'));
            }
        }

        $filterIds = $this->getFilterIds();
        if (\is_array($selected)) {
            $filterIds = array_unique(array_merge($filterIds, $selected));
        }
        $collection->addFieldToFilter(
            $collection->getResource()->getIdFieldName(),
            ['in' => $filterIds]
        );

        return $collection;
    }

    private function getFilterIds()
    {
        $idsArray = [];
        $this->applySelectionOnTargetProvider();
        if ($this->getDataProvider() instanceof \Magento\Ui\DataProvider\AbstractDataProvider) {
            // Use collection's getAllIds for optimization purposes.
            $idsArray = $this->getDataProvider()->getAllIds();
        } else {
            $dataProvider = $this->getDataProvider();
            $dataProvider->setLimit(0, false);
            $searchResult = $dataProvider->getSearchResult();
            foreach ($searchResult->getItems() as $item) {
                if ($item instanceof Entity) {
                    $idsArray[] = $item->getProductId();
                } else {
                    /** @var \Magento\Framework\Api\Search\DocumentInterface $item */
                    $idsArray[] = $item->getId();
                }
            }
        }
        return  $idsArray;
    }

    public function applySelectionOnTargetProvider()
    {
        $selected = $this->request->getParam(static::SELECTED_PARAM);
        $excluded = $this->request->getParam(static::EXCLUDED_PARAM);
        if ('false' === $excluded) {
            return;
        }
        $dataProvider = $this->getDataProvider();
        try {
            if (is_array($excluded) && !empty($excluded)) {
                $this->filterBuilder->setConditionType('nin')
                                    ->setField($dataProvider->getPrimaryFieldName())
                                    ->setValue($excluded);
                $dataProvider->addFilter($this->filterBuilder->create());
            } elseif (is_array($selected) && !empty($selected)) {
                $this->filterBuilder->setConditionType('in')
                                    ->setField($dataProvider->getPrimaryFieldName())
                                    ->setValue($selected);
                $dataProvider->addFilter($this->filterBuilder->create());
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    private function getDataProvider()
    {
        if (!$this->dataProvider) {
            $component = $this->getComponent();
            $this->prepareComponent($component);
            $this->dataProvider = $component->getContext()->getDataProvider();
        }

        return $this->dataProvider;
    }
}
