<?php

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder;

class Description extends AbstractDataBuilder
{
    public const NICK = 'Description';

    private string $onlineDescription;

    public function getBuilderData(): array
    {
        $this->searchNotFoundAttributes($this->getListingProduct()->getMagentoProduct());

        $listingProduct = $this->getListingProduct();

        $data = $listingProduct->getRenderedDescription();

        $this->processNotFoundAttributes((string)__('Description'), $this->getListingProduct()->getMagentoProduct());

        $this->onlineDescription = \M2E\TikTokShop\Model\Product::createOnlineDescription($data);

        return [
            'description' => $data,
        ];
    }

    public function getMetaData(): array
    {
        return [
            self::NICK => ['online_description' => $this->onlineDescription],
        ];
    }
}
