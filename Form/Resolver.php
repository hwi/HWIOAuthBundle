<?php

namespace HWI\Bundle\OAuthBundle\Form;

class Resolver
{
    private $container;
    private $type;

    public function __construct($container, $type)
    {
        $this->container = $container;
        $this->type = $type;
    }

    public function resolve()
    {
        if ($this->type) {
            return $this->container->get('form.factory')
                ->create($this->type, null, ['data_class' => 'Juramy\Wannahaves\UserBundle\Entity\User']);
        }

        // enable compatibility with FOSUserBundle 1.3.x and 2.x
        if (interface_exists('FOS\UserBundle\Form\Factory\FactoryInterface')) {
            return $this->container->get('hwi_oauth.registration.form.factory')->createForm();
        } else {
            return $this->container->get('hwi_oauth.registration.form');
        }
    }
}
