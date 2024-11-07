<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Create;

/**
 * Class \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Create\Breadcrumb
 */
class Breadcrumb extends \M2E\TikTokShop\Block\Adminhtml\Widget\Breadcrumb
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->setId('ttsListingBreadcrumb');

        $this->setSteps(
            [
                [
                    'id' => 1,
                    'title' => __('Step 1'),
                    'description' => __('General Settings'),
                ],
                [
                    'id' => 2,
                    'title' => __('Step 2'),
                    'description' => __('Policies'),
                ],
            ]
        );
    }

    //########################################
}
