<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\EventListener;

use HWI\Bundle\OAuthBundle\Event\RequestEvent;
use HWI\Bundle\OAuthBundle\HWIOAuthEvents;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth1ResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\OAuthAuthenticationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ErrorListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            HWIOAuthEvents::RESOURCE_OWNER_INITIALIZE => 'handle',
            HWIOAuthEvents::RESOURCE_OWNER_COMPLETE   => 'validate',
        );
    }

    /**
     * @param RequestEvent $event
     *
     * @throws OAuthAuthenticationException If an OAuth error was found
     */
    public function handle(RequestEvent $event)
    {
        $request = $event->getRequest();
        $error   = null;

        // Try to parse content if error was not in request query
        if ($request->query->has('error')) {
            $content = json_decode($request->getContent(), true);
            if (JSON_ERROR_NONE === json_last_error() && isset($content['error'])) {
                if (isset($content['error']['message'])) {
                    throw $this->createAuthenticationException($content['error']['message']);
                }

                if (isset($content['error']['code'])) {
                    $error = $content['error']['code'];
                } elseif (isset($content['error']['error-code'])) {
                    $error = $content['error']['error-code'];
                } else {
                    $error = $request->query->get('error');
                }
            } else {
                $error = $content;
            }
        } elseif ($request->query->has('oauth_problem')) {
            $error = $request->query->get('oauth_problem');
        }

        if (null !== $error) {
            throw $this->createAuthenticationException(rawurldecode($error));
        }
    }

    /**
     * @param GenericEvent $event the 'parsed' content based on the response headers
     *
     * @throws OAuthAuthenticationException If an OAuth error occurred or no access token is found
     */
    public function validate(GenericEvent $event)
    {
        $resourceOwner = $event->getSubject();
        if ($resourceOwner instanceof GenericOAuth2ResourceOwner) {
            if (isset($event['error_description'])) {
                throw $this->createAuthenticationException(null, array('error' => $event['error_description']));
            }

            if (isset($event['error'])) {
                throw $this->createAuthenticationException(null, array('error' => isset($event['error']['message']) ? $event['error']['message'] : $event['error']));
            }

            if (!isset($event['access_token'])) {
                throw $this->createAuthenticationException('not_a_valid_access_token');
            }
        } elseif ($resourceOwner instanceof GenericOAuth1ResourceOwner) {
            if (isset($event['oauth_problem'])) {
                throw $this->createAuthenticationException(null, array('error' => $event['oauth_problem']));
            }

            if (!isset($event['oauth_token']) || !isset($event['oauth_token_secret'])) {
                throw $this->createAuthenticationException('not_a_valid_access_token');
            }

            if (isset($event['oauth_callback_confirmed']) && ($event['oauth_callback_confirmed'] != 'true')) {
                throw $this->createAuthenticationException('callback_not_confirmed');
            }
        }
    }

    /**
     * @param string $message
     * @param array  $params
     *
     * @return OAuthAuthenticationException
     */
    private function createAuthenticationException($message, array $params = array())
    {
        $exception = new OAuthAuthenticationException();
        $exception->setMessageKey($message);
        $exception->setMessageData($params);

        return $exception;
    }
}
