<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle,
    Symfony\Component\HttpKernel\Kernel,
    Symfony\Component\DependencyInjection\ContainerBuilder;

use HWI\Bundle\OAuthBundle\DependencyInjection\HWIOAuthExtension,
    HWI\Bundle\OAuthBundle\DependencyInjection\CompilerPass\SetResourceOwnerServiceNameCompilerPass,
    HWI\Bundle\OAuthBundle\DependencyInjection\Security\Factory\OAuthFactory;

/**
 * HWIOAuthBundle
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <geoffrey.bachelet@gmail.com>
 */
class HWIOAuthBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // We can only register the security listener factory in sf2.1
        // If you're using 2.0, import the security_factory.xml in your security.yml:
        //
        //     factories:
        //             - "%kernel.root_dir%/../../vendor/bundles/HWI/Bundle/OAuthBundle/Resources/config/security_factory.xml"
        if (version_compare(Kernel::VERSION, '2.1-DEV', '>=')) {
            $extension = $container->getExtension('security');
            $extension->addSecurityListenerFactory(new OAuthFactory());
        }
        $container->addCompilerPass(new SetResourceOwnerServiceNameCompilerPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        // return the right extension instead of "auto-registering" it. Now the
        // alias can be hwi_oauth instead of hwi_o_auth..
        if (null === $this->extension) {
            return new HWIOAuthExtension();
        }

        return $this->extension;
    }
}
