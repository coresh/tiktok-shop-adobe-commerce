<?php

namespace M2E\TikTokShop\Model\Image\Manager;

class UploadedImage
{
    private string $nick;
    private string $uri;
    private string $url;
    private string $type;

    public function __construct(string $nick, string $uri, string $url, string $type)
    {
        $this->nick = $nick;
        $this->uri = $uri;
        $this->url = $url;
        $this->type = $type;
    }

    public function getNick(): string
    {
        return $this->nick;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
