<?php

namespace EdgarEz\SiteBuilderBundle;

use EdgarEz\SiteBuilderBundle\DependencyInjection\Security\PolicyProvider\SiteBuilderPolicyProvider;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EdgarEzSiteBuilderBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var EzPublishCoreExtension $eZExtension */
        $eZExtension = $container->getExtension('ezpublish');
        $eZExtension->addPolicyProvider(new SiteBuilderPolicyProvider($this->getPath()));
    }
}
