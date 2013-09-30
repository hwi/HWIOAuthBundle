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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Translation\TranslatorInterface;

class ErrorListener implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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
     * @throws AuthenticationException If an OAuth error was found
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
                    throw $this->throwException($content['error']['message']);
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
            throw $this->throwException(rawurldecode($error));
        }
    }

    /**
     * @param GenericEvent $event the 'parsed' content based on the response headers
     *
     * @throws AuthenticationException If an OAuth error occurred or no access token is found
     */
    public function validate(GenericEvent $event)
    {
        if (isset($event['error_description'])) {
            throw $this->throwException('unknown', array('error' => $event['error_description']));
        }

        if (isset($event['error'])) {
            throw $this->throwException('unknown', array('error' => isset($event['error']['message']) ? $event['error']['message'] : $event['error']));
        }

        if (!isset($event['access_token'])) {
            throw $this->throwException('not_a_valid_access_token');
        }
    }

    private function throwException($message, array $params = array())
    {
        return new AuthenticationException($this->translator->trans($message, $params, 'ResourceOwnersErrors'));
    }
}
