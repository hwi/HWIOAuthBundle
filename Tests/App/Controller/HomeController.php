<?php

namespace HWI\Bundle\OAuthBundle\Tests\App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class HomeController
{
    /** @var Environment */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function login()
    {
        return new Response($this->twig->render('@Acme/login.html.twig'));
    }

    public function index()
    {
        return new Response('Hello, this is the homepage');
    }
}
