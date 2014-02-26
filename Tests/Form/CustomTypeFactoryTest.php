<?php

namespace HWI\Bundle\OAuthBundle\Tests\Form;

use HWI\Bundle\OAuthBundle\Form\CustomTypeFactory;

class CustomTypeFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatePassesTypeToFormFactory()
    {
        if (!interface_exists('Symfony\Component\Form\FormFactoryInterface')) {
            $this->markTestSkipped('Symfony Form component not installed');
        }

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
