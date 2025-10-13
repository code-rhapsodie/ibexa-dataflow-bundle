<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Controller;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Entity\ScheduledDataflow;
use CodeRhapsodie\DataflowBundle\ExceptionsHandler\ExceptionHandlerInterface;
use CodeRhapsodie\DataflowBundle\ExceptionsHandler\NullExceptionHandler;
use CodeRhapsodie\IbexaDataflowBundle\CodeRhapsodieIbexaDataflowBundle;
use CodeRhapsodie\IbexaDataflowBundle\Form\CreateOneshotType;
use CodeRhapsodie\IbexaDataflowBundle\Form\CreateScheduledType;
use CodeRhapsodie\IbexaDataflowBundle\Form\UpdateScheduledType;
use CodeRhapsodie\IbexaDataflowBundle\Gateway\ExceptionJSONDecoderAdapter;
use CodeRhapsodie\IbexaDataflowBundle\Gateway\JobGateway;
use CodeRhapsodie\IbexaDataflowBundle\Gateway\ScheduledDataflowGateway;
use CodeRhapsodie\IbexaDataflowBundle\Gateway\TransformingAdapter;
use Doctrine\DBAL\Query\QueryBuilder;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\Core\Ibexa;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use Pagerfanta\Doctrine\DBAL\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/ibexa_dataflow')]
class DashboardController extends Controller
{
    public function __construct(
        private readonly JobGateway $jobGateway,
        private readonly ScheduledDataflowGateway $scheduledDataflowGateway,
        private readonly ExceptionHandlerInterface $exceptionHandler
    )
    {
    }

    #[Route(path: '/', name: 'coderhapsodie.ibexa_dataflow.main')]
    public function main(): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'view'));

        $data = [
            'product' => CodeRhapsodieIbexaDataflowBundle::PRODUCT_NAME,
            'version' => CodeRhapsodieIbexaDataflowBundle::VERSION,
            'php' => PHP_VERSION,
            'ibexa' => Ibexa::VERSION,
        ];

        return $this->render('@ibexadesign/ibexa_dataflow/Dashboard/main.html.twig', [
            'link' => 'https://www.code-rhapsodie.fr/product/redirect/'.str_replace('=', '',
                base64_encode(json_encode($data))
            ),
        ]);
    }

    public function repeating(Request $request): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'view'));

        $newWorkflow = new ScheduledDataflow();
        $newWorkflow->setNext((new \DateTimeImmutable())->add(new \DateInterval('PT1H')));
        $form = $this->createForm(CreateScheduledType::class, $newWorkflow, [
            'action' => $this->generateUrl('coderhapsodie.ibexa_dataflow.workflow.create'),
        ]);
        $updateForm = $this->createForm(UpdateScheduledType::class);

        return $this->render('@ibexadesign/ibexa_dataflow/Dashboard/repeating.html.twig', [
            'pager' => $this->getPager($this->scheduledDataflowGateway->getListQueryForAdmin(), $request),
            'form' => $form->createView(),
            'update_form' => $updateForm->createView(),
        ]);
    }

    #[Route(path: '/repeating', name: 'coderhapsodie.ibexa_dataflow.repeating')]
    public function getRepeatingPage(Request $request): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'view'));

        return $this->render('@ibexadesign/ibexa_dataflow/Dashboard/repeating.html.twig', [
            'pager' => $this->getPager($this->scheduledDataflowGateway->getListQueryForAdmin(), $request),
        ]);
    }

    public function oneshot(Request $request): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'view'));

        $newOneshotJob = new Job();
        $newOneshotJob->setRequestedDate((new \DateTime())->add(new \DateInterval('PT1H')));
        $form = $this->createForm(CreateOneshotType::class, $newOneshotJob, [
            'action' => $this->generateUrl('coderhapsodie.ibexa_dataflow.job.create'),
        ]);

        return $this->render('@ibexadesign/ibexa_dataflow/Dashboard/oneshot.html.twig', [
            'pager' => $this->getPager($this->jobGateway->getOneshotListQueryForAdmin(), $request, Job::class),
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/oneshot', name: 'coderhapsodie.ibexa_dataflow.oneshot')]
    public function getOneshotPage(Request $request): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'view'));

        return $this->render('@ibexadesign/ibexa_dataflow/Dashboard/oneshot.html.twig', [
            'pager' => $this->getPager($this->jobGateway->getOneshotListQueryForAdmin(), $request, Job::class),
        ]);
    }

    #[Route(path: '/history', name: 'coderhapsodie.ibexa_dataflow.history')]
    public function getHistoryPage(Request $request): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'view'));
        $filter = (int) $request->query->get('filter', JobGateway::FILTER_NONE);

        return $this->render('@ibexadesign/ibexa_dataflow/Dashboard/history.html.twig', [
            'pager' => $this->getPager($this->jobGateway->getListQueryForAdmin($filter), $request, Job::class),
            'filter' => $filter,
        ]);
    }

    #[Route(path: '/history/schedule/{id}', name: 'coderhapsodie.ibexa_dataflow.history.workflow')]
    public function getHistoryForScheduled(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'view'));

        return $this->render('@ibexadesign/ibexa_dataflow/Dashboard/schedule_history.html.twig', [
            'id' => $id,
            'pager' => $this->getPager($this->jobGateway->getListQueryForScheduleAdmin($id), $request, Job::class),
        ]);
    }

    private function getPager(QueryBuilder $query, Request $request, string $class = null): Pagerfanta
    {
        $adatapter = new ExceptionJSONDecoderAdapter(
            new QueryAdapter($query, fn($queryBuilder) => $queryBuilder->select('COUNT(DISTINCT id) AS total_results')
                ->resetQueryPart('orderBy')
                ->setMaxResults(1))
        );

        if ($class === Job::class && !$this->exceptionHandler instanceof NullExceptionHandler) {
            $adatapter = new TransformingAdapter($adatapter, function (array $value) {
                $exceptions = $this->exceptionHandler->find((int)$value['id']);
                $value['exceptions'] = $exceptions;
                $value['total_results'] = \count($exceptions);

                return $value;
            });
        }

        $pager = new Pagerfanta($adatapter);
        $pager->setMaxPerPage(20);
        $pager->setCurrentPage($request->query->getInt('page', 1));

        return $pager;
    }

    public function dashboard(): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('ibexa_dataflow', 'view'));

        return $this->render('@ibexadesign/ibexa_dataflow/Dashboard/dashboard.html.twig', [
            'jobs' => $this->jobGateway->getListPendindOrRunning(),
        ]);
    }
}
