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

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use HWI\Bundle\OAuthBundle\HWIOAuthBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Controller\UserValueResolver;

class AppKernel extends Kernel
{
    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new SecurityBundle(),
            new MonologBundle(),
            new TwigBundle(),
            new DoctrineBundle(),
            new HWIOAuthBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config.yml');

        if (Kernel::VERSION_ID >= 60000) {
            $loader->load(__DIR__.'/security_v6.yaml');
        } else {
            $loader->load(__DIR__.'/security_v4.yaml');
        }
    }

    public function prepareContainer(ContainerBuilder $container): void
    {
        parent::prepareContainer($container);

        // With Symfony 5.3+, session settings were changed
        if (Kernel::VERSION_ID >= 50300) {
            $container->prependExtensionConfig('framework', [
                'session' => [
                    'enabled' => true,
                    'storage_factory_id' => 'session.storage.factory.mock_file',
                ],
            ]);
        } else {
            $container->prependExtensionConfig('framework', [
                'session' => [
                    'enabled' => true,
                    'storage_id' => 'session.storage.mock_file',
                ],
            ]);
        }

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
