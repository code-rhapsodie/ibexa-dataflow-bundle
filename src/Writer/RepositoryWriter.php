<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Writer;

use CodeRhapsodie\DataflowBundle\DataflowType\Writer\WriterInterface;
use CodeRhapsodie\IbexaDataflowBundle\UserSwitcher\UserSwitcherAwareInterface;
use CodeRhapsodie\IbexaDataflowBundle\UserSwitcher\UserSwitcherAwareTrait;

abstract class RepositoryWriter implements WriterInterface, UserSwitcherAwareInterface
{
    use UserSwitcherAwareTrait;

    public function prepare()
    {
        $this->userSwitcher->switchToAdmin();
    }

    public function finish()
    {
        $this->userSwitcher->switchBack();
    }
}
class_alias(RepositoryWriter::class, 'CodeRhapsodie\EzDataflowBundle\Writer\RepositoryWriter');
