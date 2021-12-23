<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class HomeController
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function login(): Response
    {
        return new Response($this->twig->render('@Acme/login.html.twig'));
    }

    public function index(): Response
    {
        return new Response('Hello, this is the homepage');
    }

    public function privatePage(): Response
    {
        return new Response('Hello, this is some private homepage');
    }
}
