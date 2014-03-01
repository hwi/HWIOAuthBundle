<?php

namespace HWI\Bundle\OAuthBundle\Form;

use Symfony\Component\Form\FormInterface;

class LegacyFOSUBFactory implements FactoryInterface
{
    /**
     * @var \Symfony\Component\Form\FormInterface
     */
    private $form;

    public function __construct(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function create()
    {
        return $this->form;
    }
}
