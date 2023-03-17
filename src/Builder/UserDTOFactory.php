<?php

namespace App\Builder;

use App\DTO\UserDTO\UserDTO;
use App\Entity\User;

class UserDTOFactory
{
    public function getDTOFromUser(User $user): UserDTO
    {
        return new UserDTO(
            $user->getId(),
            $user->getEmail(),
            $user->getUsername(),
            $user->getRoles(),
            $user->getPassword(),
            $user->getToken()
        );
    }
}
