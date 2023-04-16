<?php

namespace App\Service;

use App\Entity\User;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class TokenService
{
    public function __construct(
        private readonly JWTTokenManagerInterface $tokenManager,
        private readonly RefreshTokenManagerInterface $refreshTokenManager
    ) {
    }

    public function createToken(
        User $user
    ): array {
        $token = $this->tokenManager->create($user);
        $refreshToken = $this->refreshTokenManager->create();
        $refreshToken->setUsername($user->getUsername());
        $refreshToken->setRefreshToken(bin2hex(random_bytes(16)));
        $validityPeriod = new \DateTime('+31 days');
        $refreshToken->setValid($validityPeriod);
        $this->refreshTokenManager->save($refreshToken);

        //Test for cookie
        $cookie = new Cookie(
            'jwt_token', //cookie name
            $token, //cookie value
            time() + 3600, //expiration time
            '/', //path
            null,
            true,
            true
        );

        $response = new Response();
        $response->headers->setCookie($cookie);

        return [
            'token' => $token,
            'refresh_token' => $refreshToken->getRefreshToken(),
            'cookie' => $cookie];
    }
}
