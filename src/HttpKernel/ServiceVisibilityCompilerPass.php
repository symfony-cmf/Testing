<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Testing\HttpKernel;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class ServiceVisibilityCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $ids = ['doctrine_phpcr'];

        foreach ($ids as $id) {
            if ($container->has($id)) {
                $definition = $container->getDefinition($id);
                $definition->setPublic(true);
            }
        }

        $aliases = ['doctrine_phpcr.session'];
        foreach ($aliases as $alias) {
            if ($container->has($id)) {
                $definition = $container->getAlias($alias);
                $definition->setPublic(true);
            }
        }
    }
}
