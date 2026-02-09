<?php

return [
    'name'        => 'Zender SMS',
    'description' => 'SMS transport for Zender API (zender.hollandworx.nl)',
    'version'     => '1.0.0',
    'author'      => 'Radata',

    'services' => [
        'integrations' => [
            'mautic.integration.zendersms' => [
                'class'     => \MauticPlugin\ZenderSmsBundle\Integration\ZenderSmsIntegration::class,
                'arguments' => [
                    'event_dispatcher',
                    'mautic.helper.cache_storage',
                    'doctrine.orm.entity_manager',
                    'request_stack',
                    'router',
                    'translator',
                    'monolog.logger.mautic',
                    'mautic.helper.encryption',
                    'mautic.lead.model.lead',
                    'mautic.lead.model.company',
                    'mautic.helper.paths',
                    'mautic.core.model.notification',
                    'mautic.lead.model.field',
                    'mautic.plugin.model.integration_entity',
                    'mautic.lead.model.dnc',
                    'mautic.lead.field.fields_with_unique_identifier',
                ],
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
