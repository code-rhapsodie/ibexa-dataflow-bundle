<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\UserSwitcher;

trait UserSwitcherAwareTrait
{
    /** @var UserSwitcherInterface */
    protected $userSwitcher;

    public function setUserSwitcher(UserSwitcherInterface $userSwitcher): void
    {
        $this->userSwitcher = $userSwitcher;
    }
}

class_alias(UserSwitcherAwareTrait::class, 'CodeRhapsodie\EzDataflowBundle\UserSwitcher\UserSwitcherAwareTrait');
