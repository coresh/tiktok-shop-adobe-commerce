<?php

namespace M2E\TikTokShop\Model\ControlPanel\Inspection;

class Issue
{
    /** @var string|null */
    private $message;

    /** @var array|string|null */
    private $metadata;

    public function __construct($message, $metadata)
    {
        $this->message = $message;
        $this->metadata = $metadata;
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return array|string|null
     */
    public function getMetadata()
    {
        if (empty($this->metadata)) {
            return '';
        }

        if (is_array($this->metadata)) {
            if (is_int(key($this->metadata))) {
                return '<pre>' . implode(PHP_EOL, $this->metadata) . ' </pre>';
            }

            return '<pre>' . str_replace('Array', '', print_r($this->metadata, true)) . '</pre>';
        }

        return $this->metadata;
    }
}
