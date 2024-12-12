<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Tab;

use CodeRhapsodie\IbexaDataflowBundle\Controller\DashboardController;
use Ibexa\Contracts\AdminUi\Tab\AbstractControllerBasedTab;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class HistoryTab extends AbstractControllerBasedTab implements OrderedTabInterface
{
    /**
     * {@inheritdoc}
     */
    public function getControllerReference(array $parameters): ControllerReference
    {
        return new ControllerReference(DashboardController::class.'::history', [], $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder(): int
    {
        return 20;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'code-rhapsodie-ibexa_dataflow-history';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->translator->trans('coderhapsodie.ibexa_dataflow.history');
    }
}
