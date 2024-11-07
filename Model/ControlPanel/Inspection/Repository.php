<?php

namespace M2E\TikTokShop\Model\ControlPanel\Inspection;

/**
 * Class \M2E\TikTokShop\Model\ControlPanel\Inspection\Repository
 */
class Repository
{
    /** @var \M2E\TikTokShop\Model\ControlPanel\Inspection\Definition[] */
    private $definitions;

    public function __construct(
        \M2E\TikTokShop\Model\ControlPanel\Inspection\Repository\DefinitionProvider $definitionProvider
    ) {
        foreach ($definitionProvider->getDefinitions() as $definition) {
            $this->definitions[$definition->getNick()] = $definition;
        }
    }

    /**
     * @param string $nick
     *
     * @return \M2E\TikTokShop\Model\ControlPanel\Inspection\Definition
     */
    public function getDefinition($nick)
    {
        return $this->definitions[$nick];
    }

    /**
     * @return \M2E\TikTokShop\Model\ControlPanel\Inspection\Definition[]
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }
}
