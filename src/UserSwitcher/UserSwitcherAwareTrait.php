<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\UserSwitcher;

trait UserSwitcherAwareTrait
{
    protected UserSwitcherInterface $userSwitcher;

    public function setUserSwitcher(UserSwitcherInterface $userSwitcher): void
    {
        $this->userSwitcher = $userSwitcher;
    }
}
