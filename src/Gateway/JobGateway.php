<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Gateway;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Gateway\JobGateway as JobGatewayDataflow;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use Doctrine\DBAL\Query\QueryBuilder;

final readonly class JobGateway
{
    public const int FILTER_NONE = 0;
    public const int FILTER_NON_EMPTY = 1;

    public function __construct(private JobRepository $jobRepository, private JobGatewayDataflow $jobGateway)
    {
    }

    public function find(int $id): ?Job
    {
        return $this->jobGateway->find($id);
    }

    public function getOneshotListQueryForAdmin(): QueryBuilder
    {
        return $this->jobRepository->createQueryBuilder('i')
            ->andWhere('i.scheduled_dataflow_id IS NULL')
            ->addOrderBy('i.requested_date', 'DESC');
    }

    public function getListQueryForAdmin(int $filter): QueryBuilder
    {
        $qb = $this->jobRepository->createQueryBuilder('w')
            ->addOrderBy('w.requested_date', 'DESC')
        ;

        if (self::FILTER_NON_EMPTY === $filter) {
            $qb->andWhere('w.count > 0');
        }

        return $qb;
    }

    public function getListQueryForScheduleAdmin(int $id): QueryBuilder
    {
        return $this->jobRepository->createQueryBuilder('w')
            ->where('w.scheduled_dataflow_id = :schedule_id')
            ->setParameter('schedule_id', $id)
            ->addOrderBy('w.requested_date', 'DESC');
    }

    public function save(Job $job)
    {
        $this->jobGateway->save($job);
    }

    public function getListPendindOrRunning(): array
    {
        $qb = $this->jobRepository->createQueryBuilder('w');
        return $qb->andWhere($qb->expr()->in('w.status', [Job::STATUS_RUNNING, Job::STATUS_PENDING, Job::STATUS_QUEUED]))
            ->orderBy('w.requested_date', 'ASC')
            ->fetchAllAssociative();
    }

    public function delete(Job $job): void
    {
        $this->jobRepository->delete($job->getId());
    }
}
