<?php

namespace EdgarEz\SiteBuilderBundle\Twig\Extension;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\API\Repository\Values\User\Policy;
use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class SecurityExtension extends \Twig_Extension
{
    /** @var TokenStorage $tokenStorage */
    protected $tokenStorage;

    /** @var AuthorizationChecker $authorizationChecker */
    protected $authorizationChecker;

    /** @var RoleService $roleService */
    protected $roleService;

    public function __construct(
        TokenStorage $tokenStorage,
        AuthorizationChecker $authorizationChecker,
        RoleService $roleService
    )
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->roleService = $roleService;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'edgarezsb_security_twig_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'sb_can',
                array($this, 'checkAuthorization')
            ),
        );
    }

    public function checkAuthorization($func)
    {
        if (!$this->authorizationChecker->isGranted(new Attribute('sitebuilder', $func))) {
            return false;
        }

        /**
         * Users with policies module *, function * will not have access
         * to functions sitegenerate and siteactivate from sitebuilder module.
         */
        if ($func == 'sitegenerate' || $func == 'siteactivate') {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();
            /** @var Policy[] $policies */
            $policies = $this->roleService->loadPoliciesByUserId($user->getAPIUser()->id);
            foreach ($policies as $policy) {
                if ($policy->module == '*' && $policy->function == '*') {
                    return false;
                }
            }
        }

        return true;
    }
}
