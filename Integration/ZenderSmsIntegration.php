<?php

namespace MauticPlugin\ZenderSmsBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ZenderSmsIntegration extends AbstractIntegration
{
    protected bool $coreIntegration = false;

    public function getName(): string
    {
        return 'ZenderSms';
    }

    public function getDisplayName(): string
    {
        return 'Zender SMS';
    }

    public function getSecretKeys(): array
    {
        return ['password'];
    }

    public function getRequiredKeyFields(): array
    {
        return [
            'password' => 'zender_sms.config.api_secret',
        ];
    }

    public function getAuthenticationType(): string
    {
        return 'none';
    }

    public function appendToForm(&$builder, $data, $formArea): void
    {
        if ('features' !== $formArea) {
            return;
        }

        $builder->add(
            'mode',
            ChoiceType::class,
            [
                'choices' => [
                    'Devices (linked Android)' => 'devices',
                    'Credits (gateway)'        => 'credits',
                ],
                'label'    => 'zender_sms.config.mode',
                'required' => true,
                'attr'     => ['class' => 'form-control'],
            ]
        );

        $builder->add(
            'device',
            TextType::class,
            [
                'label'    => 'zender_sms.config.device',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control',
                    'placeholder' => 'Linked device unique ID (for devices mode)',
                ],
            ]
        );

        $builder->add(
            'gateway',
            TextType::class,
            [
                'label'    => 'zender_sms.config.gateway',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control',
                    'placeholder' => 'Gateway or partner device ID (for credits mode)',
                ],
            ]
        );

        $builder->add(
            'sim',
            ChoiceType::class,
            [
                'choices' => [
                    'SIM 1' => '1',
                    'SIM 2' => '2',
                ],
                'label'       => 'zender_sms.config.sim',
                'required'    => false,
                'placeholder' => 'Default',
                'attr'        => ['class' => 'form-control'],
            ]
        );

        $builder->add(
            'priority',
            ChoiceType::class,
            [
                'choices' => [
                    'High (send immediately)' => '1',
                    'Normal (queued)'         => '2',
                ],
                'label'    => 'zender_sms.config.priority',
                'required' => false,
                'attr'     => ['class' => 'form-control'],
            ]
        );

        $builder->add(
            'api_url',
            TextType::class,
            [
                'label'    => 'zender_sms.config.api_url',
                'required' => false,
                'data'     => $data['api_url'] ?? 'https://zender.hollandworx.nl',
                'attr'     => [
                    'class'       => 'form-control',
                    'placeholder' => 'https://zender.hollandworx.nl',
                ],
            ]
        );
    }
}
