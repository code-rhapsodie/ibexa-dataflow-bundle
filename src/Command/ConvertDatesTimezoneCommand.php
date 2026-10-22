<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Command;

use CodeRhapsodie\DataflowBundle\Repository\JobRepository;
use CodeRhapsodie\DataflowBundle\Repository\ScheduledDataflowRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'code-rhapsodie:ibexa-dataflow:convert-dates',
    description: 'Converts the dates filled by the forms (before the UTC storage) to UTC',
    help: <<<'TXT'
The <info>%command.name%</info> command converts to UTC the dates that were filled by the forms of this bundle
(scheduled dataflows <comment>next</comment>, jobs <comment>requested_date</comment>) before dates were stored in UTC.

Those forms stored the picked instant in an inconsistent way: the UTC wall time shifted by the difference between the PHP
timezone and the timezone of the user who filled the form. The original instant can be recovered only if you tell which
timezone the users had at that time with <comment>--user-timezone</comment>, and which PHP timezone was configured with
<comment>--php-timezone</comment> (default: the current PHP default timezone).

The jobs <comment>start_time</comment> and <comment>end_time</comment> are written by PHP: convert them with
<info>code-rhapsodie:dataflow:convert-dates-to-utc</info> from the dataflow-bundle.
Rows are not tagged by their origin: rows created by the CLI or by PHP code are converted too, and would be shifted.

Dates are stored without timezone: this command is NOT idempotent, run it only once and make a backup of the tables first.
Use <comment>--dry-run</comment> to see what would be changed.
TXT,
)]
final class ConvertDatesTimezoneCommand extends Command
{
    private const BATCH_SIZE = 1000;

    private const COLUMNS = [
        ScheduledDataflowRepository::TABLE_NAME => ['next'],
        JobRepository::TABLE_NAME => ['requested_date'],
    ];

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addOption('user-timezone', null, InputOption::VALUE_REQUIRED, 'Timezone of the users who filled the forms')
            ->addOption('php-timezone', null, InputOption::VALUE_REQUIRED, 'PHP timezone when the forms were filled (default: the PHP default timezone)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only display what would be changed')
        ;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            if (!$input->getOption('user-timezone')) {
                throw new \InvalidArgumentException('The --user-timezone option is required.');
            }
            $userTimezone = new \DateTimeZone($input->getOption('user-timezone'));
            $phpTimezone = new \DateTimeZone($input->getOption('php-timezone') ?? date_default_timezone_get());
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return self::INVALID;
        }

        $dryRun = $input->getOption('dry-run');
        $io->note(sprintf('Dates filled by users in "%s" with PHP in "%s" will be converted to UTC%s.', $userTimezone->getName(), $phpTimezone->getName(), $dryRun ? ' (dry run)' : ''));

        if (!$dryRun && !$io->confirm('The conversion is not idempotent: have you made a backup and do you want to continue?', false)) {
            return self::FAILURE;
        }

        foreach (self::COLUMNS as $table => $columns) {
            $total = $this->convertTable($table, $columns, $phpTimezone, $userTimezone, $dryRun);
            $io->writeln(sprintf('%s: %d row(s) %s.', $table, $total, $dryRun ? 'would be updated' : 'updated'));
        }

        return self::SUCCESS;
    }

    /**
     * @param string[] $columns
     */
    private function convertTable(string $table, array $columns, \DateTimeZone $phpTimezone, \DateTimeZone $userTimezone, bool $dryRun): int
    {
        $select = implode(', ', ['id', ...$columns]);
        $lastId = 0;
        $updated = 0;

        do {
            $rows = $this->connection->fetchAllAssociative(
                sprintf('SELECT %s FROM %s WHERE id > ? ORDER BY id ASC LIMIT %d', $select, $table, self::BATCH_SIZE),
                [$lastId],
            );

            $this->connection->beginTransaction();
            try {
                foreach ($rows as $row) {
                    $lastId = (int) $row['id'];
                    $changes = [];
                    foreach ($columns as $column) {
                        if ($row[$column] === null) {
                            continue;
                        }
                        // The stored value read in the PHP timezone, as a wall time in the user timezone, is the UTC wall time of the picked instant
                        $converted = new \DateTimeImmutable($row[$column], $phpTimezone)->setTimezone($userTimezone)->format('Y-m-d H:i:s');
                        if ($converted !== $row[$column]) {
                            $changes[$column] = $converted;
                        }
                    }

                    if ($changes === []) {
                        continue;
                    }
                    ++$updated;
                    if (!$dryRun) {
                        $this->connection->update($table, $changes, ['id' => $lastId]);
                    }
                }
                $this->connection->commit();
            } catch (\Throwable $e) {
                $this->connection->rollBack();

                throw $e;
            }
        } while (\count($rows) === self::BATCH_SIZE);

        return $updated;
    }
}
