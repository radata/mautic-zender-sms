<?php

namespace MauticPlugin\ZenderSmsBundle\Transport;

use Mautic\PluginBundle\Helper\IntegrationHelper;

class Configuration
{
    private string $apiSecret = '';
    private string $apiUrl    = 'https://zender.hollandworx.nl';
    private string $mode      = 'credits';
    private string $device    = '';
    private string $gateway   = '';
    private string $sim       = '';
    private string $priority  = '2';
    private bool $configured  = false;

    public function __construct(
        private IntegrationHelper $integrationHelper,
    ) {
    }

    public function getApiSecret(): string
    {
        $this->setConfiguration();

        return $this->apiSecret;
    }

    public function getApiUrl(): string
    {
        $this->setConfiguration();

        return rtrim($this->apiUrl, '/');
    }

    public function getMode(): string
    {
        $this->setConfiguration();

        return $this->mode;
    }

    public function getDevice(): string
    {
        $this->setConfiguration();

        return $this->device;
    }

    public function getGateway(): string
    {
        $this->setConfiguration();

        return $this->gateway;
    }

    public function getSim(): string
    {
        $this->setConfiguration();

        return $this->sim;
    }

    public function getPriority(): string
    {
        $this->setConfiguration();

        return $this->priority;
    }

    /**
     * @throws ConfigurationException
     */
    private function setConfiguration(): void
    {
        if ($this->configured) {
            return;
        }

        $integration = $this->integrationHelper->getIntegrationObject('ZenderSms');
        if (!$integration || !$integration->getIntegrationSettings()->getIsPublished()) {
            throw new ConfigurationException('Zender SMS integration is not enabled');
        }

        $keys = $integration->getDecryptedApiKeys();
        if (empty($keys['password'])) {
            throw new ConfigurationException('Zender API secret is not configured');
        }

        $this->apiSecret = $keys['password'];

        $features        = $integration->getIntegrationSettings()->getFeatureSettings();
        $this->apiUrl    = !empty($features['api_url']) ? $features['api_url'] : 'https://zender.hollandworx.nl';
        $this->mode      = !empty($features['mode']) ? $features['mode'] : 'credits';
        $this->device    = $features['device'] ?? '';
        $this->gateway   = $features['gateway'] ?? '';
        $this->sim       = $features['sim'] ?? '';
        $this->priority  = $features['priority'] ?? '2';

        $this->configured = true;
    }
}
