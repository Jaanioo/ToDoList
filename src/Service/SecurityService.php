<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class SecurityService
{
    public function getCurrentUserId(Security $security): ?int
    {
        // Get the current user from the security context
        $currentUser = $security->getUser();

        // Check if the current user is authorized to access the requested resource
        if (!$currentUser instanceof User) {
            return null;
        }

        return $currentUser->getId();
    }
}
