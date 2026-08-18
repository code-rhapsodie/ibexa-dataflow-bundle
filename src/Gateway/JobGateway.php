<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Gateway;

use CodeRhapsodie\DataflowBundle\Entity\Job;
use CodeRhapsodie\DataflowBundle\Gateway\JobGateway as JobGatewayDataflow;
use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use Doctrine\DBAL\ArrayParameterType;
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

    public function getListQueryForAdmin(int $filter, string $type): QueryBuilder
    {
        $qb = $this->jobRepository->createQueryBuilder('w')
            ->addOrderBy('w.requested_date', 'DESC')
        ;

        if (self::FILTER_NON_EMPTY === $filter) {
            $qb->andWhere('w.count > 0');
        }

        if (!empty($type)) {
           $qb->andWhere('w.dataflow_type = :type')
               ->setParameter('type', $type);
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

    /**
     * @param array<int> $scheduleIds
     *
     * @return array<int, float>
     */
    public function getAverageExecutionTimes(array $scheduleIds): array
    {
        if (empty($scheduleIds)) {
            return [];
        }

        $results = $this->jobRepository->createQueryBuilder('j')
            ->select(
                'j.scheduled_dataflow_id',
                'AVG(TIMESTAMPDIFF(SECOND, j.start_time, j.end_time)) AS avg_time'
            )
            ->andWhere('j.scheduled_dataflow_id IN (:ids)')
            ->andWhere('j.start_time IS NOT NULL')
            ->andWhere('j.end_time IS NOT NULL')
            ->andWhere('j.status = :status')
            ->setParameter('ids', $scheduleIds, ArrayParameterType::INTEGER)
            ->setParameter('status', Job::STATUS_COMPLETED)
            ->groupBy('j.scheduled_dataflow_id')
            ->executeQuery()
            ->fetchAllKeyValue();

        return array_map('floatval', $results);
    }

    /**
     * @param array<int> $status
     */
    public function counts(array $status): array
    {
        $qb = $this->jobRepository->createQueryBuilder('w')->groupBy('w.status');

        if (!empty($status)) {
           $qb->andWhere('w.status IN (:status)')
               ->setParameter('status', $status, ArrayParameterType::INTEGER);
        }

        return $qb->select('w.status, COUNT(w.id) as count')->fetchAllAssociative();
    }
}
