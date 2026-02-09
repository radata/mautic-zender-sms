<?php

namespace MauticPlugin\ZenderSmsBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use MauticPlugin\ZenderSmsBundle\Form\Type\SendSmsType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SmsController extends FormController
{
    public function sendSmsAction(Request $request, $objectId = ''): JsonResponse|Response
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
                        ['action' => $route, 'sms_choices' => $choices]
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
            $smsId = $data['smsId'] ?? null;

            if (!$smsId) {
                $this->addFlashMessage('zender_sms.send.error.no_sms', [], 'error');

                return new JsonResponse(['closeModal' => true, 'flashes' => $this->getFlashContent()]);
            }

            $sms = $smsModel->getEntity((int) $smsId);

            if (!$sms || !$sms->isPublished()) {
                $this->addFlashMessage('zender_sms.send.error.not_found', [], 'error');

                return new JsonResponse(['closeModal' => true, 'flashes' => $this->getFlashContent()]);
            }

            $result     = $smsModel->sendSms($sms, $lead, ['channel' => 'page']);
            $leadResult = $result[$lead->getId()] ?? null;

            if ($leadResult && !empty($leadResult['sent'])) {
                $this->addFlashMessage('zender_sms.send.success');
            } else {
                $status = $leadResult['status'] ?? 'zender_sms.send.error.failed';
                $this->addFlashMessage($status, [], 'error');
            }

            return new JsonResponse(['closeModal' => true, 'flashes' => $this->getFlashContent()]);
        }

        return new Response('Bad Request', 400);
    }
}
