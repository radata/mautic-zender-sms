<?php

return [
    'name'        => 'Zender SMS',
    'description' => 'SMS transport for Zender API (zender.hollandworx.nl)',
    'version'     => '1.0.0',
    'author'      => 'Hollandworx',

    'services' => [
        'integrations' => [
            'mautic.integration.zendersms' => [
                'class' => \MauticPlugin\ZenderSmsBundle\Integration\ZenderSmsIntegration::class,
            ],
        ],
        'others' => [
            'mautic.sms.transport.zender.configuration' => [
                'class'     => \MauticPlugin\ZenderSmsBundle\Transport\Configuration::class,
                'arguments' => [
                    'mautic.helper.integration',
                ],
            ],
            'mautic.sms.transport.zender' => [
                'class'     => \MauticPlugin\ZenderSmsBundle\Transport\ZenderTransport::class,
                'arguments' => [
                    'mautic.sms.transport.zender.configuration',
                    'monolog.logger.mautic',
                ],
                'tag'          => 'mautic.sms_transport',
                'tagArguments' => [
                    'channel'          => 'ZenderSms',
                    'integrationAlias' => 'ZenderSms',
                ],
            ],
        ],
    ],
];
