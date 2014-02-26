<?php

namespace HWI\Bundle\OAuthBundle\Tests\Form;

use HWI\Bundle\OAuthBundle\Form\FOSUBFactory;

class FOSUBFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateCallsFOSUBFormFactoryCreate()
    {
        if (!interface_exists('Symfony\Component\Form\FormFactoryInterface')) {
            $this->markTestSkipped('Symfony Form component not installed');
        }

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
