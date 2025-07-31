<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Controller;

use CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow;
use CodeRhapsodie\IbexaDataflowBundle\Form\CreateScheduledType;
use CodeRhapsodie\IbexaDataflowBundle\Form\UpdateScheduledType;
use CodeRhapsodie\IbexaDataflowBundle\Gateway\ScheduledDataflowGateway;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\NotificationHandlerInterface;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/ibexa_dataflow/scheduled_workflow')]
class ScheduledDataflowController extends Controller
{
    public function __construct(private readonly NotificationHandlerInterface $notificationHandler, private readonly ScheduledDataflowGateway $scheduledDataflowGateway, private readonly TranslatorInterface $translator)
    {
    }

    #[Route(path: '/create', name: 'coderhapsodie.ibexa_dataflow.workflow.create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'edit'));

        $newWorkflow = new ScheduledDataflow();
        $form = $this->createForm(CreateScheduledType::class, $newWorkflow);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow $newWorkflow */
            $newWorkflow = $form->getData();
            try {
                $this->scheduledDataflowGateway->save($newWorkflow);
                $this->notificationHandler->success($this->translator->trans('coderhapsodie.ibexa_dataflow.workflow.create.success'));
            } catch (\Exception $e) {
                $this->notificationHandler->error($this->translator->trans('coderhapsodie.ibexa_dataflow.workflow.create.error',
                    ['message' => $e->getMessage()]));
            }

            return new JsonResponse(['redirect' => $this->generateUrl('coderhapsodie.ibexa_dataflow.main', ['_fragment' => 'ibexa-tab-coderhapsodie-ibexa_dataflow-code-rhapsodie-ibexa_dataflow-repeating'])]);
        }

        return new JsonResponse([
            'form' => $this->renderView('@ibexadesign/ibexa_dataflow/parts/form_modal.html.twig', [
                'form' => $form->createView(),
                'type_action' => 'new',
            ]),
        ]);
    }

    #[Route(path: '/{id}/delete', name: 'coderhapsodie.ibexa_dataflow.workflow.delete', methods: ['post'])]
    public function delete(int $id): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'edit'));

        try {
            $this->scheduledDataflowGateway->delete($id);
            $this->notificationHandler->success($this->translator->trans('coderhapsodie.ibexa_dataflow.workflow.delete.success'));
        } catch (\Exception $e) {
            $this->notificationHandler->error($this->translator->trans('coderhapsodie.ibexa_dataflow.workflow.delete.error',
                ['message' => $e->getMessage()]));
        }

        return $this->redirectToRoute('coderhapsodie.ibexa_dataflow.main', ['_fragment' => 'ibexa-tab-coderhapsodie-ibexa_dataflow-code-rhapsodie-ibexa_dataflow-repeating']);
    }

    #[Route(path: '/{id}/edit', name: 'coderhapsodie.ibexa_dataflow.workflow.edit')]
    public function edit(Request $request, int $id): Response
    {
        $form = $this->createForm(UpdateScheduledType::class, $this->scheduledDataflowGateway->find($id), [
            'action' => $this->generateUrl('coderhapsodie.ibexa_dataflow.workflow.edit', ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow $editDataflow */
            $editDataflow = $form->getData();

            try {
                $this->scheduledDataflowGateway->save($editDataflow);
                $this->notificationHandler->success($this->translator->trans('coderhapsodie.ibexa_dataflow.workflow.edit.success'));
            } catch (\Exception $e) {
                $this->notificationHandler->error($this->translator->trans('coderhapsodie.ibexa_dataflow.workflow.edit.error',
                    ['message' => $e->getMessage()]));
            }

            return new JsonResponse(['redirect' => $this->generateUrl('coderhapsodie.ibexa_dataflow.main', ['_fragment' => 'ibexa-tab-coderhapsodie-ibexa_dataflow-code-rhapsodie-ibexa_dataflow-repeating'])]);
        }

        return new JsonResponse([
            'form' => $this->renderView('@ibexadesign/ibexa_dataflow/parts/form_modal.html.twig', [
                'form' => $form->createView(),
                'type_action' => 'edit',
            ]),
        ]);
    }

    #[Route(path: '/{id}/enable', name: 'coderhapsodie.ibexa_dataflow.workflow.enable')]
    public function enableDataflow(int $id): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'edit'));

        $this->changeDataflowStatus($id, true);

        return $this->redirectToRoute('coderhapsodie.ibexa_dataflow.main', ['_fragment' => 'ibexa-tab-coderhapsodie-ibexa_dataflow-code-rhapsodie-ibexa_dataflow-repeating']);
    }

    private function changeDataflowStatus(int $id, bool $status)
    {
        try {
            /** @var \CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow $workflow */
            $workflow = $this->scheduledDataflowGateway->find($id);
            $workflow->setEnabled($status);
            $this->scheduledDataflowGateway->save($workflow);

            $this->notificationHandler->success(sprintf('Workflow "%s" updated successfully.', $workflow->getLabel()));
        } catch (\Exception $e) {
            $this->notificationHandler->error(sprintf('An error occured : "%s".', $e->getMessage()));
        }
    }

    #[Route(path: '/{id}/disable', name: 'coderhapsodie.ibexa_dataflow.workflow.disable')]
    public function disableDataflow(int $id): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'edit'));

        $this->changeDataflowStatus($id, false);

        return $this->redirectToRoute('coderhapsodie.ibexa_dataflow.main', ['_fragment' => 'ibexa-tab-coderhapsodie-ibexa_dataflow-code-rhapsodie-ibexa_dataflow-repeating']);
    }
}
