<?php

namespace App\DTO\UserDTO;

use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordUserDTO
{
    public function __construct(
        /**
         * @Assert\NotBlank()
         * @Assert\Email()
         */
        public string $email,
        /**
         * @Assert\NotBlank()
         * @Assert\Length(min=8, max=255)
         */
        public string $password
    ) {
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

}
