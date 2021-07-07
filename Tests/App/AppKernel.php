<?php

declare(strict_types=1);

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\App;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Controller\UserValueResolver;

class AppKernel extends Kernel
{
    public function registerBundles(): array
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
        ];

        if (class_exists(\FOS\UserBundle\FOSUserBundle::class)) {
            $bundles[] = new \FOS\UserBundle\FOSUserBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config.yml');
    }

    public function prepareContainer(ContainerBuilder $container): void
    {
        parent::prepareContainer($container);

        if (method_exists(Security::class, 'getUser') && !class_exists(UserValueResolver::class)) {
            $container->loadFromExtension('security', [
                'firewalls' => [
                    'login_area' => [
                        'logout_on_user_change' => true,
                    ],
                    'main' => [
                        'logout_on_user_change' => true,
                    ],
                ],
            ]);
        }
    }

    public function getCacheDir(): string
    {
        return $this->getBaseDir().'cache';
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getLogDir(): string
    {
        return $this->getBaseDir().'log';
    }

    protected function getBaseDir(): string
    {
        return sys_get_temp_dir().'/hwioauth-bundle/'.(new \ReflectionClass($this))->getShortName().'/var/';
    }
}
