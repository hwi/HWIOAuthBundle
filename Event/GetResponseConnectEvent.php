<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetResponseConnectEvent extends RequestEvent
{
    /**
     * @var null|Response
     */
    protected $response;

    /**
     * @var \Exception|string
     */
    protected $error;

    /**
     * @param Request           $request
     * @param string|\Exception $error
     */
    public function __construct(Request $request, $error)
    {
        parent::__construct($request);

        $this->error = $error;
    }

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

    /**
     * @return Boolean
     */
    public function hasResponse()
    {
        return null !== $this->response;
    }
}
