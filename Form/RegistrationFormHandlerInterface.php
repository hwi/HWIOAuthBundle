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

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * RegistrationFormHandlerInterface.
 *
 * Interface for objects that are able to handle a form.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
interface RegistrationFormHandlerInterface
{
    /**
     * Processes the form for a given request.
     *
     * @param Request               $request         Active request
     * @param Form                  $form            Form to process
     * @param UserResponseInterface $userInformation OAuth response
     *
     * @return bool True if the processing was successful
     */
    public function process(Request $request, Form $form, UserResponseInterface $userInformation);
}
