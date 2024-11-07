<?php

namespace M2E\TikTokShop\Model\Response;

class Message
{
    public const TEXT_KEY = 'text';
    public const TYPE_KEY = 'type';

    public const TYPE_ERROR = 'error';
    public const TYPE_WARNING = 'warning';
    public const TYPE_SUCCESS = 'success';
    public const TYPE_NOTICE = 'notice';

    protected $text = '';
    protected $type = null;

    public static function createError(string $text): self
    {
        $obj = new static();
        $obj->text = $text;
        $obj->type = self::TYPE_ERROR;

        return $obj;
    }

    public static function createWarning(string $text): self
    {
        $obj = new static();
        $obj->text = $text;
        $obj->type = self::TYPE_WARNING;

        return $obj;
    }

    public static function createNotice(string $text): self
    {
        $obj = new static();
        $obj->text = $text;
        $obj->type = self::TYPE_NOTICE;

        return $obj;
    }

    public static function createSuccess(string $text): self
    {
        $obj = new static();
        $obj->text = $text;
        $obj->type = self::TYPE_SUCCESS;

        return $obj;
    }

    public static function create(string $text, string $type): self
    {
        $obj = new static();
        $obj->text = $text;
        $obj->type = $type;

        return $obj;
    }

    public function initFromResponseData(array $responseData)
    {
        $this->text = $responseData[self::TEXT_KEY];
        $this->type = $responseData[self::TYPE_KEY];
    }

    public function initFromPreparedData($text, $type)
    {
        $this->text = $text;
        $this->type = $type;
    }

    public function initFromException(\Exception $exception)
    {
        $this->text = $exception->getMessage();
        $this->type = self::TYPE_ERROR;
    }

    //########################################

    public function asArray()
    {
        return [
            self::TEXT_KEY => $this->text,
            self::TYPE_KEY => $this->type,
        ];
    }

    //########################################

    public function getText()
    {
        return $this->text;
    }

    public function getType()
    {
        return $this->type;
    }

    //########################################

    public function isError()
    {
        return $this->type == self::TYPE_ERROR;
    }

    public function isWarning()
    {
        return $this->type == self::TYPE_WARNING;
    }

    public function isSuccess()
    {
        return $this->type == self::TYPE_SUCCESS;
    }

    public function isNotice()
    {
        return $this->type == self::TYPE_NOTICE;
    }

    //########################################
}
