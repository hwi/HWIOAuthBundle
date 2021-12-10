<?php

declare(strict_types=1);

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\App\Form;

use HWI\Bundle\OAuthBundle\Form\RegistrationFormHandlerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\User;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

final class RegistrationFormHandler implements RegistrationFormHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(Request $request, FormInterface $form, UserResponseInterface $userInformation)
    {
        $form->setData(new User());

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            return $form->isValid();
        }

        return false;
    }
}
