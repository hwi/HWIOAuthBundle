<?php

namespace HWI\Bundle\OAuthBundle\Form;

use FOS\UserBundle\Form\Factory\FactoryInterface as FOSFormFactory;

class FOSUBFactory implements FactoryInterface
{
    private $formFactory;

    public function __construct(FOSFormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->formFactory->createForm();
    }
}
