<?php

namespace ZfThreeSocialAuth\Service;

use Zend\Authentication\Result;

/**
 * The AuthManager service is responsible for user's login and registration through social OAuth
 */
class SocialAuthManager
{

    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager 
     */
    private $entityManager;
    private $authService;

    /**
     *
     * @var string
     */
    private $userClass;

    public function __construct($entityManager, $userClass, $authService)
    {
        $this->entityManager = $entityManager;
        $this->userClass = $userClass;
        $this->authService = $authService;
    }

    public function completeSocialLogin($clientRequestResult)
    {

        $email = $clientRequestResult['email'];
        $user = $this->entityManager->getRepository($this->userClass)
                ->findOneByEmail($email);
        if (null != $user) {
            return $this->signUserIn($user);
        }
        return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND, null, ['The e-mail address "' . $email . '" does not have an account.']);
    }

    public function completeSocialRegistration($clientRequestResult)
    {
        // check to see if this e-mail address has account and fail 
        $user = $this->entityManager->getRepository($this->userClass)
                ->findOneByEmail($clientRequestResult['email']);
        if (null == $user) {
            return $this->createNewUser($clientRequestResult);
        }
        return new Result(
                Result::FAILURE, null, ['The e-mail address "' . $clientRequestResult['email'] . '" already has an account.']);
    }

    protected function signUserIn($user)
    {
        if ($this->authService->hasIdentity()) {
            $this->authService->clearIdentity();
        }
        $this->authService->getStorage()->write($user->getEmail());
        // die($this->authService->getIdentity());
        return new Result(
                Result::SUCCESS, $user->getEmail(), ['Authenticated successfully.']);
    }

    protected function createNewUser($clientRequestResult)
    {
        $user = new $this->userClass();
        $user->setEmail($clientRequestResult['email']);
        $user->setFullName($clientRequestResult['name']);
        $user->setPassword($clientRequestResult['provider']);
        $user->setStatus(1);
        $user->setDateCreated(date('Y-m-d H:i:s'));
        // Add the entity to the entity manager.
        $this->entityManager->persist($user);
        // Apply changes to database.
        $this->entityManager->flush();
        return $this->signUserIn($user);
    }

    public function completeSocialLoginOrRegistration($clientRequestResult)
    {
        // check to see if this e-mail address has account and fail 
        $user = $this->entityManager->getRepository($this->userClass)
                ->findOneByEmail($clientRequestResult['email']);
        if (null == $user) {
            return $this->createNewUser($clientRequestResult);
        } else {
            return $this->signUserIn($user);
        }
    }

    public function test()
    {
        die($this->authService->getIdentity());
    }

}
