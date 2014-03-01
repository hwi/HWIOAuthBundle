<?php

namespace HWI\Bundle\OAuthBundle\Tests\Form;

use HWI\Bundle\OAuthBundle\Form\LegacyFOSUBFactory;

class LegacyFOSUBFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateReturnsLegacyFOSUBRegistrationForm()
    {
        if (interface_exists('FOS\UserBundle\Form\Factory\FactoryInterface')) {
            $this->markTestSkipped('Legacy FOSUserBundle 1.x not installed');
        }

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $sut = new LegacyFOSUBFactory($form);
        $result = $sut->create();

        $this->assertSame($form, $result);
    }
}
