<?php
namespace HWI\Bundle\OAuthBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class FilterUserResponseEvent extends UserEvent
{
    private $response;

    public function __construct(UserInterface $user, Request $request, Response $response)
    {
        parent::__construct($user, $request);
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}