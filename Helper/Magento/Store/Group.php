<?php

namespace M2E\TikTokShop\Helper\Magento\Store;

class Group
{
    /** @var \Magento\Store\Api\Data\GroupInterface */
    private $defaultStoreGroup;
    /** @var \Magento\Catalog\Model\CategoryFactory */
    private $catalogCategoryFactory;
    /** @var \Magento\Store\Model\GroupFactory */
    private $storeGroupFactory;
    /** @var \Magento\Store\Model\StoreManagerInterface */
    private $storeManager;
    /** @var \M2E\TikTokShop\Helper\Magento\Store\Website */
    private $websiteHelper;

    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Magento\Store\Model\GroupFactory $storeGroupFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \M2E\TikTokShop\Helper\Magento\Store\Website $websiteHelper
    ) {
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        $this->storeGroupFactory = $storeGroupFactory;
        $this->storeManager = $storeManager;
        $this->websiteHelper = $websiteHelper;
    }

    // ----------------------------------------

    /**
     * @param $entity
     *
     * @return bool
     */
    public function isExists($entity)
    {
        if ($entity instanceof \Magento\Store\Model\Group) {
            return (bool)$entity->getCode();
        }

        try {
            $this->storeManager->getGroup($entity);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function isChildOfWebsite($groupId, $websiteId)
    {
        $group = $this->storeManager->getGroup($groupId);

        return ($group->getWebsiteId() == $websiteId);
    }

    // ----------------------------------------

    /**
     * @return \Magento\Store\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDefault(): \Magento\Store\Api\Data\GroupInterface
    {
        if ($this->defaultStoreGroup === null) {
            $defaultWebsite = $this->storeManager->getWebsite(true);
            $defaultStoreGroupId = $defaultWebsite->getDefaultGroupId();

            $this->defaultStoreGroup = $this->storeManager->getGroup($defaultStoreGroupId);
        }

        return $this->defaultStoreGroup;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDefaultGroupId(): int
    {
        return (int)$this->getDefault()->getId();
    }

    public function addGroup($websiteId, $name, $rootCategoryId)
    {
        if (!$this->websiteHelper->isExists($websiteId)) {
            $error = (string)__(
                'Website with id %value does not exist.',
                ['value' => (int)$websiteId]
            );
            throw new \M2E\TikTokShop\Model\Exception($error);
        }

        $group = $this->storeGroupFactory->create();
        $group->setId(null);
        $group->setName($name);

        $group->setWebsiteId($websiteId);
        $group->setWebsite($this->storeManager->getWebsite($websiteId));

        if (isset($rootCategoryId)) {
            $category = $this->catalogCategoryFactory->create()->load($rootCategoryId);

            if (!$category->hasEntityId()) {
                $error = (string)__(
                    'Category with %category_id doen\'t exist',
                    ['category_id' => $rootCategoryId],
                );
                throw new \M2E\TikTokShop\Model\Exception($error);
            }

            if ((int)$category->getLevel() !== 1) {
                $error = (string)__('Category of level 1 must be provided.');
                throw new \M2E\TikTokShop\Model\Exception($error);
            }

            $group->setRootCategoryId($rootCategoryId);
        }

        $group->save();

        return $group;
    }

    public function getGroups($withDefault = false)
    {
        return $this->storeManager->getGroups($withDefault);
    }
}
