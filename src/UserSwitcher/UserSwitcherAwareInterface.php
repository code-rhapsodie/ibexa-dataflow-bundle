<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\UserSwitcher;

interface UserSwitcherAwareInterface
{
    public function setUserSwitcher(UserSwitcherInterface $userSwitcher): void;
}
class_alias(UserSwitcherAwareInterface::class, 'CodeRhapsodie\EzDataflowBundle\UserSwitcher\UserSwitcherAwareInterface');
