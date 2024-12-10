<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\UserSwitcher;

interface UserSwitcherInterface
{
    public function switchToAdmin(): void;

    public function switchTo($loginOrId): void;

    public function switchBack(): void;
}
class_alias(UserSwitcherInterface::class, 'CodeRhapsodie\EzDataflowBundle\UserSwitcher\UserSwitcherInterface');
