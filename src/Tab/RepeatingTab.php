<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Tab;

use CodeRhapsodie\IbexaDataflowBundle\Controller\DashboardController;
use Ibexa\Contracts\AdminUi\Tab\AbstractControllerBasedTab;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class RepeatingTab extends AbstractControllerBasedTab implements OrderedTabInterface
{
    public function getControllerReference(array $parameters): ControllerReference
    {
        return new ControllerReference(DashboardController::class.'::repeating');
    }

    public function getOrder(): int
    {
        return 0;
    }

    public function getIdentifier(): string
    {
        return 'code-rhapsodie-ibexa_dataflow-repeating';
    }

    public function getName(): string
    {
        return $this->translator->trans('coderhapsodie.ibexa_dataflow.repeating');
    }
}
