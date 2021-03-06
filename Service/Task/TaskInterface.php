<?php

namespace EdgarEz\SiteBuilderBundle\Service\Task;

use Symfony\Component\DependencyInjection\Container;

interface TaskInterface
{
    public function validateParameters($parameters);

    public function execute($command, array $parameters, Container $container);

    public function getMessage();
}
