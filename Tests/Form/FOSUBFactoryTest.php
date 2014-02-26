<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Form;

use HWI\Bundle\OAuthBundle\Form\FOSUBFactory;

class FOSUBFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateCallsFOSUBFormFactoryCreate()
    {
        if (!interface_exists('FOS\UserBundle\Form\Factory\FactoryInterface')) {
            $this->markTestSkipped('FOSUserBundle 2.x not installed');
        }

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $formFactory = $this->getMock('FOS\UserBundle\Form\Factory\FactoryInterface');
        $formFactory->expects($this->once())
            ->method('createForm')
            ->will($this->returnValue($form));

        $sut = new FOSUBFactory($formFactory);
        $result = $sut->create();

        $this->assertSame($form, $result);
    }
}
