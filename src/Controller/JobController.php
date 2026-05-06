<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Controller;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\IbexaDataflowBundle\Form\CreateOneshotType;
use CodeRhapsodie\IbexaDataflowBundle\Gateway\JobGateway;
use CodeRhapsodie\IbexaDataflowBundle\Gateway\ScheduledDataflowGateway;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\NotificationHandlerInterface;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/ibexa_dataflow/job')]
class JobController extends Controller
{
    public function __construct(private readonly JobGateway $jobGateway, private readonly NotificationHandlerInterface $notificationHandler, private readonly TranslatorInterface $translator, private readonly ScheduledDataflowGateway $scheduledDataflowGateway)
    {
    }

    #[Route(path: '/details/{id}', name: 'coderhapsodie.ibexa_dataflow.job.details')]
    public function displayDetails(int $id): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'view'));

        return $this->render('@ibexadesign/ibexa_dataflow/Item/details.html.twig', [
            'item' => $this->jobGateway->find($id),
        ]);
    }

    #[Route(path: '/details/log/{id}', name: 'coderhapsodie.ibexa_dataflow.job.log')]
    public function displayLog(int $id): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'view'));
        $item = $this->jobGateway->find($id);
        $log = array_map(fn($line) => preg_replace('~#\d+~', "\n$0", (string) $line), $item->getExceptions() ?? []);

        if (!empty($log) && $item->getStreamExceptions()) {
            return new StreamedResponse(function () use ($item) {
                while (($line = fgets($item->getStreamExceptions())) !== false) {
                    echo "<p>",preg_replace('~#\d+~', "<br>$0", (string) $line),"</p>";
                    flush();
                }
            });
        }

        return $this->render('@ibexadesign/ibexa_dataflow/Item/log.html.twig', [
            'log' => $log,
        ]);
    }

    #[Route(path: '/create', name: 'coderhapsodie.ibexa_dataflow.job.create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'edit'));

        $newOneshot = new Job();
        $form = $this->createForm(CreateOneshotType::class, $newOneshot);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \CodeRhapsodie\DataflowBundle\Entity\Job $newOneshot */
            $newOneshot = $form->getData();
            $newOneshot->setStatus(Job::STATUS_PENDING);

            try {
                $this->jobGateway->save($newOneshot);
                $this->notificationHandler->success($this->translator->trans('coderhapsodie.ibexa_dataflow.job.create.success'));
            } catch (\Exception $e) {
                $this->notificationHandler->error($this->translator->trans('coderhapsodie.ibexa_dataflow.job.create.error',
                    ['message' => $e->getMessage()]));
            }

            return new JsonResponse([
                'redirect' => $this->generateUrl('coderhapsodie.ibexa_dataflow.main', ['_fragment' => 'ibexa-tab-coderhapsodie-ibexa_dataflow-code-rhapsodie-ibexa_dataflow-oneshot'],
                    UrlGeneratorInterface::ABSOLUTE_URL),
            ]);
        }

        return new JsonResponse([
            'form' => $this->renderView('@ibexadesign/ibexa_dataflow/parts/form_modal.html.twig', [
                'form' => $form->createView(),
                'type_action' => 'new',
                'mode' => 'oneshot',
            ]),
        ]);
    }

    #[Route(path: '/run-oneshot/{id}', name: 'coderhapsodie.ibexa_dataflow.job.run-oneshot', methods: ['GET'])]
    public function runOneShot(int $id): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'view'));

        $scheduledDataflow = $this->scheduledDataflowGateway->find($id);

        if ($scheduledDataflow === null) {
            throw new NotFoundHttpException();
        }

        $newOneshotJob = new Job();
        $newOneshotJob->setOptions($scheduledDataflow->getOptions());
        $newOneshotJob->setLabel("Manual " . $scheduledDataflow->getLabel());
        $newOneshotJob->setScheduledDataflowId($scheduledDataflow->getId());
        $newOneshotJob->setRequestedDate((new \DateTime())->add(new \DateInterval('PT1H')));
        $newOneshotJob->setDataflowType($scheduledDataflow->getDataflowType());

        $form = $this->createForm(CreateOneshotType::class, $newOneshotJob, [
            'action' => $this->generateUrl('coderhapsodie.ibexa_dataflow.job.create'),
        ]);

        return new JsonResponse([
            'form' => $this->renderView('@ibexadesign/ibexa_dataflow/parts/form_modal.html.twig', [
                'form' => $form->createView(),
                'id' => 'modal-new-oneshot',
                'mode' => 'oneshot',
            ]),
        ]);
    }

    #[Route(path: '/delete/{id}', name: 'coderhapsodie.ibexa_dataflow.job.delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'edit'));

        $job = $this->jobGateway->find($id);

        if ($job === null || $job->getScheduledDataflowId() !== null || $job->getStatus() !== Job::STATUS_PENDING) {
            throw new NotFoundHttpException();
        }

        $this->jobGateway->delete($job);
        $this->notificationHandler->success($this->translator->trans('coderhapsodie.ibexa_dataflow.job.delete.success'));

        return $this->redirectToRoute('coderhapsodie.ibexa_dataflow.main');
    }
}
