<?php

namespace HWI\Bundle\OAuthBundle\Form;

use Symfony\Component\Form\FormFactoryInterface;

class CustomTypeFactory implements FactoryInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @param FormFactoryInterface $formFactory
     * @param string $type
     */
    public function __construct($type, FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->formFactory->create($this->type);
    }
}