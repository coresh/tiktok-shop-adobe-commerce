<?php

namespace M2E\TikTokShop\Block\Adminhtml\Tag;

class Switcher extends \M2E\TikTokShop\Block\Adminhtml\Switcher
{
    public const TAG_ID_REQUEST_PARAM_KEY = 'tag';

    protected $paramName = self::TAG_ID_REQUEST_PARAM_KEY;
    private \M2E\TikTokShop\Model\Tag\ListingProduct\Repository $tagRelationRepository;
    private string $label;

    public function __construct(
        string $label,
        string $controllerName,
        \M2E\TikTokShop\Model\Tag\ListingProduct\Repository $tagRelationRepository,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        /** @see parent::getSwitchUrl() */
        $this->setData('controller_name', $controllerName);

        $this->label = $label;
        $this->tagRelationRepository = $tagRelationRepository;
    }

    public function getLabel()
    {
        return $this->label;
    }

    protected function loadItems()
    {
        $tags = $this->tagRelationRepository->getTagEntitiesWithoutHasErrorsTag();

        if (empty($tags)) {
            $this->items = [];

            return;
        }

        $items = [];
        foreach ($tags as $tag) {
            $items[]['value'][] = [
                'value' => $tag->getId(),
                'label' => $tag->getErrorCode(),
            ];
        }

        $this->items = $items;
    }

    /**
     * @return string
     */
    public function getDefaultOptionName(): string
    {
        return ' ';
    }

    /**
     * @return true
     */
    public function hasDefaultOption(): bool
    {
        return true;
    }
}
