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

use HWI\Bundle\OAuthBundle\Form\CustomTypeFactory;

class CustomTypeFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatePassesTypeToFormFactory()
    {
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $typeName = 'custom_type_name';

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())
            ->method('create')
            ->with($typeName)
            ->will($this->returnValue($form));

        $sut = new CustomTypeFactory($typeName, $formFactory);
        $result = $sut->create();

        $this->assertSame($form, $result);
    }
}
