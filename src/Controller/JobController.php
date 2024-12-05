<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Controller;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\IbexaDataflowBundle\Form\CreateOneshotType;
use CodeRhapsodie\IbexaDataflowBundle\Gateway\JobGateway;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\NotificationHandlerInterface;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/ibexa_dataflow/job")
 */
class JobController extends Controller
{
    /** @var \CodeRhapsodie\IbexaDataflowBundle\Gateway\JobGateway */
    private $jobGateway;
    /** @var \Ibexa\Contracts\AdminUi\Notification\NotificationHandlerInterface */
    private $notificationHandler;
    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;

    public function __construct(
        JobGateway $jobGateway,
        NotificationHandlerInterface $notificationHandler,
        TranslatorInterface $translator
    ) {
        $this->jobGateway = $jobGateway;
        $this->notificationHandler = $notificationHandler;
        $this->translator = $translator;
    }

    /**
     * @Route("/details/{id}", name="coderhapsodie.ibexa_dataflow.job.details")
     */
    public function displayDetails(int $id): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'view'));

        return $this->render('@ibexadesign/ibexa_dataflow/Item/details.html.twig', [
            'item' => $this->jobGateway->find($id),
        ]);
    }

    /**
     * @Route("/details/log/{id}", name="coderhapsodie.ibexa_dataflow.job.log")
     */
    public function displayLog(int $id): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'view'));
        $item = $this->jobGateway->find($id);
        $log = array_map(function ($line) {
            return preg_replace('~#\d+~', "\n$0", $line);
        }, $item->getExceptions());

        return $this->render('@ibexadesign/ibexa_dataflow/Item/log.html.twig', [
            'log' => $log,
        ]);
    }

    /**
     * @Route("/create", name="coderhapsodie.ibexa_dataflow.job.create", methods={"POST"})
     */
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
}
