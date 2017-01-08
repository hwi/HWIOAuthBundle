<?php
namespace HWI\Bundle\OAuthBundle\Event;

use Symfony\Component\HttpFoundation\Response;

class GetResponseUserEvent extends UserEvent
{
    private $response;

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return Response|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}
