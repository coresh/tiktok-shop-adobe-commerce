<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Template\Synchronization;

abstract class ChangeProcessorAbstract extends \M2E\TikTokShop\Model\Template\ChangeProcessorAbstract
{
    public const INSTRUCTION_INITIATOR = 'template_synchronization_change_processor';

    public const INSTRUCTION_TYPE_LIST_MODE_ENABLED = 'template_synchronization_list_mode_enabled';
    public const INSTRUCTION_TYPE_LIST_MODE_DISABLED = 'template_synchronization_list_mode_disabled';
    public const INSTRUCTION_TYPE_LIST_SETTINGS_CHANGED = 'template_synchronization_list_settings_changed';

    public const INSTRUCTION_TYPE_RELIST_MODE_ENABLED = 'template_synchronization_relist_mode_enabled';
    public const INSTRUCTION_TYPE_RELIST_MODE_DISABLED = 'template_synchronization_relist_mode_disabled';
    public const INSTRUCTION_TYPE_RELIST_SETTINGS_CHANGED = 'template_synchronization_relist_settings_changed';

    public const INSTRUCTION_TYPE_STOP_MODE_ENABLED = 'template_synchronization_stop_mode_enabled';
    public const INSTRUCTION_TYPE_STOP_MODE_DISABLED = 'template_synchronization_stop_mode_disabled';
    public const INSTRUCTION_TYPE_STOP_SETTINGS_CHANGED = 'template_synchronization_stop_settings_changed';

    protected function getInstructionInitiator(): string
    {
        return self::INSTRUCTION_INITIATOR;
    }

    /**
     * @param \M2E\TikTokShop\Model\Template\Synchronization\Diff $diff
     * @param int $status
     *
     * @return array
     */
    protected function getInstructionsData(
        \M2E\TikTokShop\Model\ActiveRecord\Diff $diff,
        int $status
    ): array {
        $data = [];

        /** @var \M2E\TikTokShop\Model\Template\Synchronization\Diff $diff */
        if ($diff->isListModeEnabled()) {
            $priority = 0;

            if ($status === \M2E\TikTokShop\Model\Product::STATUS_NOT_LISTED) {
                $priority = 30;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_LIST_MODE_ENABLED,
                'priority' => $priority,
            ];
        }

        if ($diff->isListModeDisabled()) {
            $priority = 0;

            if ($status === \M2E\TikTokShop\Model\Product::STATUS_NOT_LISTED) {
                $priority = 5;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_LIST_MODE_DISABLED,
                'priority' => $priority,
            ];
        }

        if ($diff->isListSettingsChanged()) {
            $priority = 0;

            if ($status === \M2E\TikTokShop\Model\Product::STATUS_NOT_LISTED) {
                $priority = 20;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_LIST_SETTINGS_CHANGED,
                'priority' => $priority,
            ];
        }

        if ($diff->isRelistModeEnabled()) {
            $priority = 5;

            if (
                $status === \M2E\TikTokShop\Model\Product::STATUS_INACTIVE
            ) {
                $priority = 50;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_RELIST_MODE_ENABLED,
                'priority' => $priority,
            ];
        }

        if ($diff->isRelistModeDisabled()) {
            $data[] = [
                'type' => self::INSTRUCTION_TYPE_RELIST_MODE_DISABLED,
                'priority' => 5,
            ];
        }

        if ($diff->isRelistSettingsChanged()) {
            $priority = 5;

            if (
                $status === \M2E\TikTokShop\Model\Product::STATUS_INACTIVE
            ) {
                $priority = 40;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_RELIST_SETTINGS_CHANGED,
                'priority' => $priority,
            ];
        }

        if ($diff->isStopModeEnabled()) {
            $priority = 0;

            if ($status === \M2E\TikTokShop\Model\Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_STOP_MODE_ENABLED,
                'priority' => $priority,
            ];
        }

        if ($diff->isStopModeDisabled()) {
            $priority = 0;

            if ($status === \M2E\TikTokShop\Model\Product::STATUS_LISTED) {
                $priority = 5;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_STOP_MODE_DISABLED,
                'priority' => $priority,
            ];
        }

        if ($diff->isStopSettingsChanged()) {
            $priority = 0;

            if ($status === \M2E\TikTokShop\Model\Product::STATUS_LISTED) {
                $priority = 20;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_STOP_SETTINGS_CHANGED,
                'priority' => $priority,
            ];
        }

        return $data;
    }

    //########################################
}
