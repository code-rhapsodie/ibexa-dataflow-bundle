<?php

declare(strict_types=1);

namespace CodeRhapsodie\EzDataflowBundle\Controller;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\EzDataflowBundle\Form\CreateOneshotType;
use CodeRhapsodie\EzDataflowBundle\Gateway\JobGateway;
use CodeRhapsodie\EzDataflowBundle\Gateway\ScheduledDataflowGateway;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\NotificationHandlerInterface;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/ezdataflow/job")
 */
class JobController extends Controller
{
    /** @var \CodeRhapsodie\EzDataflowBundle\Gateway\JobGateway */
    private $jobGateway;
    /** @var \Ibexa\Contracts\AdminUi\Notification\NotificationHandlerInterface */
    private $notificationHandler;
    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;
    /** @var \CodeRhapsodie\EzDataflowBundle\Gateway\ScheduledDataflowGateway  */
    private $scheduledDataflowGateway;

    public function __construct(
        JobGateway $jobGateway,
        NotificationHandlerInterface $notificationHandler,
        TranslatorInterface $translator,
        ScheduledDataflowGateway $scheduledDataflowGateway
    ) {
        $this->jobGateway = $jobGateway;
        $this->notificationHandler = $notificationHandler;
        $this->translator = $translator;
        $this->scheduledDataflowGateway = $scheduledDataflowGateway;
    }

    /**
     * @Route("/details/{id}", name="coderhapsodie.ezdataflow.job.details")
     */
    public function displayDetails(int $id): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ezdataflow', 'view'));

        return $this->render('@ibexadesign/ezdataflow/Item/details.html.twig', [
            'item' => $this->jobGateway->find($id),
        ]);
    }

    /**
     * @Route("/details/log/{id}", name="coderhapsodie.ezdataflow.job.log")
     */
    public function displayLog(int $id): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ezdataflow', 'view'));
        $item = $this->jobGateway->find($id);
        $log = array_map(function ($line) {
            return preg_replace('~#\d+~', "\n$0", $line);
        }, $item->getExceptions());

        return $this->render('@ibexadesign/ezdataflow/Item/log.html.twig', [
            'log' => $log,
        ]);
    }

    /**
     * @Route("/create", name="coderhapsodie.ezdataflow.job.create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ezdataflow', 'edit'));

        $newOneshot = new Job();
        $form = $this->createForm(CreateOneshotType::class, $newOneshot);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \CodeRhapsodie\DataflowBundle\Entity\Job $newOneshot */
            $newOneshot = $form->getData();
            $newOneshot->setStatus(Job::STATUS_PENDING);

            try {
                $this->jobGateway->save($newOneshot);
                $this->notificationHandler->success($this->translator->trans('coderhapsodie.ezdataflow.job.create.success'));
            } catch (\Exception $e) {
                $this->notificationHandler->error($this->translator->trans('coderhapsodie.ezdataflow.job.create.error',
                    ['message' => $e->getMessage()]));
            }

            return new JsonResponse([
                'redirect' => $this->generateUrl('coderhapsodie.ezdataflow.main', ['_fragment' => 'ibexa-tab-coderhapsodie-ezdataflow-code-rhapsodie-ezdataflow-oneshot'],
                    UrlGeneratorInterface::ABSOLUTE_URL),
            ]);
        }

        return new JsonResponse([
            'form' => $this->renderView('@ibexadesign/ezdataflow/parts/form_modal.html.twig', [
                'form' => $form->createView(),
                'type_action' => 'new',
                'mode' => 'oneshot',
            ]),
        ]);
    }

    /**
     * @Route("/run-oneshot/{id}", name="coderhapsodie.ezdataflow.job.run-oneshot", methods={"GET"})
     */
    public function runOneShot(int $id): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ezdataflow', 'view'));

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
            'action' => $this->generateUrl('coderhapsodie.ezdataflow.job.create'),
        ]);

        return new JsonResponse([
            'form' => $this->renderView('@ibexadesign/ezdataflow/parts/form_modal.html.twig', [
                'form' => $form->createView(),
                'id' => 'modal-new-oneshot',
                'mode' => 'oneshot',
            ]),
        ]);
    }
}
