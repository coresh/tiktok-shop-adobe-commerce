<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Template\Compliance;

class ChangeProcessor extends \M2E\TikTokShop\Model\Template\ChangeProcessorAbstract
{
    public const INSTRUCTION_INITIATOR = 'template_compliance_change_processor';

    //########################################

    protected function getInstructionInitiator(): string
    {
        return self::INSTRUCTION_INITIATOR;
    }

    // ---------------------------------------

    /**
     * @param \M2E\TikTokShop\Model\Template\Compliance\Diff $diff
     */
    protected function getInstructionsData(
        \M2E\TikTokShop\Model\ActiveRecord\Diff $diff,
        int $status
    ): array {
        $data = [];

        /** @var \M2E\TikTokShop\Model\Template\Compliance\Diff $diff */
        if ($diff->isComplianceDataDifferent()) {
            $data[] = [
                'type' => self::INSTRUCTION_TYPE_COMPLIANCE_DATA_CHANGED,
                'priority' => 80,
            ];
        }

        return $data;
    }
}
