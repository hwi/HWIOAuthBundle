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
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles(): array
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \FOS\UserBundle\FOSUserBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
        ];

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config.yml');
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
