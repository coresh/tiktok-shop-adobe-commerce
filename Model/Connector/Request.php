<?php

namespace M2E\TikTokShop\Model\Connector;

class Request
{
    /** @var null */
    private $component = null;
    /** @var null */
    private $componentVersion = null;
    /** @var null */
    private $command = null;
    private array $input = [];
    /** @var string */
    private $platformName;
    /** @var string */
    private $platformVersion;
    /** @var string */
    private $moduleName;
    /** @var string */
    private $moduleVersion;
    /** @var string|null */
    private $locationDomain;
    /** @var string|null */
    private $locationIp;
    /** @var string */
    private $locationDirectory;
    /** @var string */
    private $applicationKey;
    /** @var string|null */
    private $licenseKey;

    public function setComponent(string $value): self
    {
        $this->component = $value;

        return $this;
    }

    public function getComponent()
    {
        return $this->component;
    }

    // ----------------------------------------

    public function setComponentVersion(int $value): self
    {
        $this->componentVersion = $value;

        return $this;
    }

    public function getComponentVersion()
    {
        return $this->componentVersion;
    }

    // ----------------------------------------

    public function setCommand(array $value): self
    {
        $value = array_values($value);

        if (count($value) !== 3) {
            throw new \LogicException('Invalid Command Format.');
        }

        $this->command = $value;

        return $this;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function setInput(array $input): self
    {
        $this->input = $input;

        return $this;
    }

    public function getInput(): array
    {
        return $this->input;
    }

    // ----------------------------------------

    public function setPlatform(string $name, string $version): self
    {
        $this->platformName = $name;
        $this->platformVersion = $version;

        return $this;
    }

    public function setModule(string $name, string $version): self
    {
        $this->moduleName = $name;
        $this->moduleVersion = $version;

        return $this;
    }

    public function setLocation(?string $domain, ?string $ip): self
    {
        $this->locationDomain = $domain;
        $this->locationIp = $ip;
        $this->locationDirectory = '/var/www';

        return $this;
    }

    public function setAuth(string $applicationKey, ?string $licenseKey): self
    {
        $this->applicationKey = $applicationKey;
        $this->licenseKey = $licenseKey;

        return $this;
    }

    public function getInfo(): array
    {
        return [
            'client' => [
                'platform' => [
                    'name' => $this->platformName,
                    'version' => $this->platformVersion,
                ],
                'module' => [
                    'name' => $this->moduleName,
                    'version' => $this->moduleVersion,
                ],
                'location' => [
                    'domain' => $this->locationDomain,
                    'ip' => $this->locationIp,
                    'directory' => $this->locationDirectory,
                ],
                'locale' => 'en',
            ],
            'auth' => [
                'application_key' => $this->applicationKey,
                'license_key' => $this->licenseKey,
            ],
            'component' => [
                'name' => $this->component,
                'version' => $this->componentVersion,
            ],
            'command' => [
                'entity' => $this->command[0],
                'type' => $this->command[1],
                'name' => $this->command[2],
            ],
        ];
    }
}
