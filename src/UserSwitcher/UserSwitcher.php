<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\UserSwitcher;

use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;

class UserSwitcher implements UserSwitcherInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\User\UserReference[] */
    private $userStack;

    /**
     * @param string|int $adminLoginOrId
     */
    public function __construct(private readonly PermissionResolver $permissionResolver, private readonly UserService $userService, private $adminLoginOrId)
    {
        $this->userStack = [];
    }

    public function switchTo($loginOrId): void
    {
        if (is_int($loginOrId)) {
            $user = $this->userService->loadUser($loginOrId);
        } else {
            $user = $this->userService->loadUserByLogin($loginOrId);
        }

        $this->userStack[] = $this->permissionResolver->getCurrentUserReference();
        $this->permissionResolver->setCurrentUserReference($user);
    }

    public function switchToAdmin(): void
    {
        $this->switchTo($this->adminLoginOrId);
    }

    public function switchBack(): void
    {
        if (empty($this->userStack)) {
            return;
        }

        $this->permissionResolver->setCurrentUserReference(array_pop($this->userStack));
    }
}
class_alias(UserSwitcher::class, 'CodeRhapsodie\EzDataflowBundle\UserSwitcher\UserSwitcher');
