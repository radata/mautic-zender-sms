<?php

namespace MauticPlugin\ZenderSmsBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use Mautic\SmsBundle\Sms\TransportChain;
use MauticPlugin\ZenderSmsBundle\Form\Type\SendSmsType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SmsController extends FormController
{
    public function sendSmsAction(Request $request, TransportChain $transportChain, $objectId = ''): JsonResponse|Response
    {
        if ('POST' === $request->getMethod()) {
            $data     = $request->request->all()['zender_send_sms'] ?? [];
            $objectId = $data['contactId'] ?? $objectId;
        }

        $leadModel = $this->getModel('lead');
        $lead      = $leadModel->getEntity($objectId);

        if (!$lead) {
            $this->addFlashMessage('mautic.lead.lead.error.notfound', [], 'error');

            return new JsonResponse(['closeModal' => true, 'flashes' => $this->getFlashContent()]);
        }

        if (!$this->security->hasEntityAccess(
            'lead:leads:editown',
            'lead:leads:editother',
            $lead->getPermissionUser()
        )) {
            $this->addFlashMessage('mautic.core.error.accessdenied', [], 'error');

            return new JsonResponse(['closeModal' => true, 'flashes' => $this->getFlashContent()]);
        }

        /** @var \Mautic\SmsBundle\Model\SmsModel $smsModel */
        $smsModel = $this->getModel('sms');

        // Build transport choices from all enabled transports
        $enabledTransports = $transportChain->getEnabledTransports();
        $transportChoices  = [];
        foreach ($transportChain->getTransports() as $alias => $transportData) {
            if (isset($enabledTransports[$alias])) {
                $transportChoices[$alias] = $transportData['alias'] ?? $alias;
            }
        }

        if ('GET' === $request->getMethod()) {
            $smsList = $smsModel->getRepository()->getSmsList('', 0, 0, true, 'template');
            $choices = [];
            foreach ($smsList as $sms) {
                $choices[$sms['id']] = $sms['name'];
            }

            $route = $this->generateUrl(
                'mautic_plugin_zendersms_action',
                ['objectAction' => 'sendSms']
            );

            return $this->delegateView([
                'viewParameters' => [
                    'form' => $this->createForm(
                        SendSmsType::class,
                        ['contactId' => (string) $objectId],
                        [
                            'action'            => $route,
                            'sms_choices'       => $choices,
                            'transport_choices'  => $transportChoices,
                        ]
                    )->createView(),
                    'contact' => $lead,
                ],
                'contentTemplate' => '@ZenderSms/SendSms/form.html.twig',
                'passthroughVars' => [
                    'activeLink'    => '#mautic_contact_index',
                    'mauticContent' => 'lead',
                    'route'         => $route,
                ],
            ]);
        }

        if ('POST' === $request->getMethod()) {
            $transportAlias = $data['transport'] ?? null;
            $smsId          = $data['smsId'] ?? null;
            $customMessage  = trim($data['customMessage'] ?? '');

            // Determine message content
            $content = null;

            if (!empty($smsId)) {
                // Template selected - get content from template
                $sms = $smsModel->getEntity((int) $smsId);
                if (!$sms || !$sms->isPublished()) {
                    $this->addFlashMessage('zender_sms.send.error.not_found', [], 'error');

                    return new JsonResponse(['closeModal' => true, 'flashes' => $this->getFlashContent()]);
                }
                $content = $sms->getMessage();
            } elseif (!empty($customMessage)) {
                $content = $customMessage;
            }

            if (empty($content)) {
                $this->addFlashMessage('zender_sms.send.error.no_content', [], 'error');

                return new JsonResponse(['closeModal' => true, 'flashes' => $this->getFlashContent()]);
            }

            // Get the selected transport
            try {
                $transport = $transportChain->getTransport($transportAlias);
            } catch (\Exception $e) {
                $this->addFlashMessage('zender_sms.send.error.transport', [], 'error');

                return new JsonResponse(['closeModal' => true, 'flashes' => $this->getFlashContent()]);
            }

            // Replace tokens in content
            $content = str_replace(
                ['{contactfield=firstname}', '{contactfield=lastname}', '{contactfield=email}', '{contactfield=phone}', '{contactfield=mobile}'],
                [$lead->getFirstname(), $lead->getLastname(), $lead->getEmail(), $lead->getPhone(), $lead->getMobile()],
                $content
            );

            // Send via the selected transport
            $result = $transport->sendSms($lead, $content);

            if (true === $result) {
                $this->addFlashMessage('zender_sms.send.success');
            } else {
                $this->addFlashMessage('zender_sms.send.error.failed_detail', ['%error%' => $result], 'error');
            }

            return new JsonResponse(['closeModal' => true, 'flashes' => $this->getFlashContent()]);
        }

        return new Response('Bad Request', 400);
    }
}
