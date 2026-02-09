<?php

namespace MauticPlugin\ZenderSmsBundle\Transport;

use Mautic\LeadBundle\Entity\Lead;
use Mautic\SmsBundle\Sms\TransportInterface;
use Psr\Log\LoggerInterface;

class ZenderTransport implements TransportInterface
{
    public function __construct(
        private Configuration $configuration,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Send an SMS via the Zender API.
     *
     * @param string $content
     *
     * @return bool|string true on success, error message on failure
     */
    public function sendSms(Lead $lead, $content)
    {
        $number = $lead->getLeadPhoneNumber();
        if (empty($number)) {
            return 'mautic.sms.transport.error.no_phone';
        }

        // Ensure E.164 format
        $number = $this->normalizePhoneNumber($number);

        try {
            $apiUrl    = $this->configuration->getApiUrl();
            $apiSecret = $this->configuration->getApiSecret();
            $mode      = $this->configuration->getMode();
        } catch (ConfigurationException $e) {
            $this->logger->warning('Zender SMS not configured: '.$e->getMessage());

            return $e->getMessage();
        }

        // Build POST fields
        $postFields = [
            'secret'  => $apiSecret,
            'mode'    => $mode,
            'phone'   => $number,
            'message' => $content,
        ];

        // Add mode-specific fields
        if ('devices' === $mode) {
            $device = $this->configuration->getDevice();
            if (empty($device)) {
                return 'Zender: device ID is required for devices mode';
            }
            $postFields['device'] = $device;

            $sim = $this->configuration->getSim();
            if (!empty($sim)) {
                $postFields['sim'] = $sim;
            }

            $priority = $this->configuration->getPriority();
            if (!empty($priority)) {
                $postFields['priority'] = $priority;
            }
        } else {
            $gateway = $this->configuration->getGateway();
            if (empty($gateway)) {
                return 'Zender: gateway ID is required for credits mode';
            }
            $postFields['gateway'] = $gateway;
        }

        // Send via cURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $apiUrl.'/api/send/sms',
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if (!empty($error)) {
            $this->logger->error('Zender SMS cURL error: '.$error);

            return 'Zender API error: '.$error;
        }

        $data = json_decode($response, true);

        if (null === $data) {
            $this->logger->error('Zender SMS invalid response: '.$response);

            return 'Zender: invalid API response';
        }

        if (200 !== ($data['status'] ?? 0)) {
            $errorMsg = $data['message'] ?? 'Unknown error';
            $this->logger->warning('Zender SMS send failed: '.$errorMsg, [
                'phone'    => $number,
                'status'   => $data['status'] ?? 'unknown',
                'response' => $response,
            ]);

            return 'Zender: '.$errorMsg;
        }

        $this->logger->info('Zender SMS sent successfully', [
            'phone'     => $number,
            'messageId' => $data['data']['messageId'] ?? null,
        ]);

        return true;
    }

    /**
     * Normalize phone number to E.164 format.
     * If already starts with +, leave as-is.
     * Otherwise assume Dutch number and prepend +31.
     */
    private function normalizePhoneNumber(string $number): string
    {
        // Strip spaces, dashes, parentheses
        $number = preg_replace('/[\s\-\(\)]/', '', $number);

        // Already E.164
        if (str_starts_with($number, '+')) {
            return $number;
        }

        // Dutch local format: 06xxxxxxxx -> +316xxxxxxxx
        if (str_starts_with($number, '0')) {
            return '+31'.substr($number, 1);
        }

        // Assume it has country code without +
        return '+'.$number;
    }
}
