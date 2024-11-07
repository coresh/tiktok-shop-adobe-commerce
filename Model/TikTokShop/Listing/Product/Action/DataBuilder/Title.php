<?php

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder;

class Title extends AbstractDataBuilder
{
    public const NICK = 'Title';

    private ?string $onlineTitle = null;

    public function getBuilderData(): array
    {
        $this->searchNotFoundAttributes($this->getListingProduct()->getMagentoProduct());
        $title = $this->getListingProduct()->getDescriptionTemplateSource()->getTitle();

        $this->processNotFoundAttributes((string)__('Title'), $this->getListingProduct()->getMagentoProduct());

        $this->onlineTitle = $title;

        return [
            'title' => $title,
        ];
    }

    public function getMetaData(): array
    {
        return [
            self::NICK => ['online_title' => $this->onlineTitle],
        ];
    }
}
