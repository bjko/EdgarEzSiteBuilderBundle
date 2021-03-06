<?php

namespace EdgarEz\SiteBuilderBundle\Controller;

use EdgarEz\SiteBuilderBundle\Form\Type\CustomerType;
use EdgarEz\SiteBuilderBundle\Form\Type\InstallType;
use EdgarEz\SiteBuilderBundle\Form\Type\ModelType;
use EdgarEz\SiteBuilderBundle\Form\Type\SiteType;
use EdgarEz\SiteBuilderBundle\Generator\CustomerGenerator;
use EdgarEz\SiteBuilderBundle\Generator\ProjectGenerator;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\User\User;
use EzSystems\PlatformUIBundle\Controller\Controller;

class SbController extends Controller
{
    /** @var LocationService $locationService */
    protected $locationService;

    protected $tabItems;

    public function __construct(LocationService $locationService, $tabItems)
    {
        $this->locationService = $locationService;
        $this->tabItems = $tabItems;
    }

    public function sbAction($tabItem)
    {
        $installed = $this->container->hasParameter('edgar_ez_site_builder.installed')
            ? $this->container->getParameter('edgar_ez_site_builder.installed')
            : false;
        $tabItems = $this->tabItems;

        if (!$installed) {
            $tabItems = array($tabItems[0], $tabItems[1]);
        } else {
            unset($tabItems[0]);
        }

        return $this->render('EdgarEzSiteBuilderBundle:sb:index.html.twig', [
            'installed' => $installed,
            'tab_items' => $tabItems,
            'tab_item_selected' => $tabItem,
            'params' => array(),
            'hasErrors' => false
        ]);
    }

    public function tabAction($tabItem, $paramsTwig = array(), $hasErrors = false)
    {
        $params = array();
        $tabItemMethod = 'tabItem' . ucfirst($tabItem);
        $params = $this->{$tabItemMethod}($paramsTwig);

        return $this->render('EdgarEzSiteBuilderBundle:sb:tab/' . $tabItem . '.html.twig', [
            'tab_items' => $this->tabItems,
            'tab_item' => $tabItem,
            'params' => $params
        ]);
    }

    protected function tabItemInstall($paramsTwig)
    {
        if (isset($paramsTwig['install'])) {
            $params['installForm'] = $paramsTwig['install'];
        } else {
            $params['installForm'] = $this->createForm(
                new InstallType()
            )->createView();
        }

        return $params;
    }

    protected function tabItemDashboard($paramsTwig)
    {
        $params['user_id'] = $this->getUser()->getAPIUser()->getUserId();
        return $params;
    }

    protected function tabItemCustomergenerate($paramsTwig)
    {
        if (isset($paramsTwig['customergenerate'])) {
            $params['customerForm'] = $paramsTwig['customergenerate'];
        } else {
            $params['customerForm'] = $this->createForm(
                new CustomerType()
            )->createView();
        }

        return $params;
    }

    protected function tabItemModelgenerate($paramsTwig)
    {
        if (isset($paramsTwig['modelgenerate'])) {
            $params['modelForm'] = $paramsTwig['modelgenerate'];
        }
        $params['modelForm'] = $this->createForm(
            new ModelType()
        )->createView();

        return $params;
    }

    protected function tabItemSitegenerate($paramsTwig)
    {
        if (isset($paramsTwig['sitegenerate'])) {
            $params['sitegenerate'] = $paramsTwig['sitegenerate'];
        } else {
            $customerName = $this->getCustomerName();

            $customerAlias = strtolower(
                ProjectGenerator::CUSTOMERS . '_' . $customerName . '_' . CustomerGenerator::SITES
            );
            $params['siteForm'] = $this->createForm(
                new SiteType(
                    $this->container->get('ezpublish.api.service.location'),
                    $this->container->get('ezpublish.api.service.search'),
                    $this->container->getParameter('edgarez_sb.project.default.models_location_id'),
                    $this->container->getParameter('edgarez_sb.project.default.media_models_location_id'),
                    $this->container->getParameter(
                        'edgarez_sb.customer.' . $customerAlias . '.default.customer_location_id'
                    ),
                    $this->container->getParameter(
                        'edgarez_sb.customer.' . $customerAlias . '.default.media_customer_location_id'
                    ),
                    $customerName
                )
            )->createView();
        }

        return $params;
    }

    protected function tabItemSiteactivate($paramsTwig)
    {
        return array();
    }

    protected function getCustomerName()
    {
        /** @var User $user */
        $user = $this->getUser();
        $userLocation = $this->locationService->loadLocation($user->getAPIUser()->contentInfo->mainLocationId);

        $parent = $this->locationService->loadLocation($userLocation->parentLocationId);
        return $parent->contentInfo->name;
    }
}
