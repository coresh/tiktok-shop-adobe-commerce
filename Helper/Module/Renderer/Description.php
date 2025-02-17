<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Helper\Module\Renderer;

class Description
{
    private \Magento\Store\Model\App\Emulation $appEmulation;
    private \Magento\Email\Model\Template\Filter $filter;

    public function __construct(
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Email\Model\Template\Filter $filter
    ) {
        $this->appEmulation = $appEmulation;
        $this->filter = $filter;
    }

    public function parseTemplate($text, \M2E\TikTokShop\Model\Magento\Product $magentoProduct)
    {
        // Start store emulation process
        $this->appEmulation->startEnvironmentEmulation(
            $magentoProduct->getStoreId(),
            \Magento\Framework\App\Area::AREA_FRONTEND,
            true
        );
        //--

        $text = $this->parseWithoutMagentoTemplate($text, $magentoProduct);

        // the CMS static block replacement i.e. {{media url=’image.jpg’}}
        $this->filter->setVariables(['product' => $magentoProduct->getProduct()]);
        $text = $this->filter->filter($text);

        //-- Stop store emulation process
        $this->appEmulation->stopEnvironmentEmulation();

        //--

        return $text;
    }

    public function parseWithoutMagentoTemplate(string $text, \M2E\TikTokShop\Model\Magento\Product $magentoProduct): string
    {
        $text = $this->insertAttributes($text, $magentoProduct);

        return $text;
    }

    private function insertAttributes($text, \M2E\TikTokShop\Model\Magento\Product $magentoProduct)
    {
        preg_match_all("/#([A-Za-z_0-9]+?)#/", $text, $matches);

        if (empty($matches[0])) {
            return $text;
        }

        $search = [];
        $replace = [];
        foreach ($matches[1] as $attributeCode) {
            $value = $magentoProduct->getAttributeValue($attributeCode);

            if ($value != '') {
                if ($attributeCode == 'weight') {
                    $value = (float)$value;
                } elseif (in_array($attributeCode, ['price', 'special_price'])) {
                    $value = $magentoProduct->getProduct()->getFormattedPrice();
                }
                $search[] = '#' . $attributeCode . '#';
                $replace[] = $value;
            } else {
                $search[] = '#' . $attributeCode . '#';
                $replace[] = '';
            }
        }

        $text = str_replace($search, $replace, $text);

        return $text;
    }
}
