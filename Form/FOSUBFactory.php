<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
