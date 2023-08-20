<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\Exception\HttpTransportException;
use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\OAuth\State\State;
use HWI\Bundle\OAuthBundle\OAuth\StateInterface;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 * @author Francisco Facioni <fran6co@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
abstract class AbstractResourceOwner implements ResourceOwnerInterface
{
    protected array $options = [];

    /**
     * @var array<string, array<int, string>|string|null>
     */
    protected array $paths = [];

    protected HttpClientInterface $httpClient;
    protected HttpUtils $httpUtils;
    protected string $name;
    protected StateInterface $state;
    protected RequestDataStorageInterface $storage;
    private bool $stateLoaded = false;

    /**
     * @param array  $options Options for the resource owner
     * @param string $name    Name for the resource owner
     */
    public function __construct(
        HttpClientInterface $httpClient,
        HttpUtils $httpUtils,
        array $options,
        string $name,
        RequestDataStorageInterface $storage
    ) {
        $this->httpClient = $httpClient;
        $this->httpUtils = $httpUtils;
        $this->name = $name;
        $this->storage = $storage;

        if (!empty($options['paths'])) {
            $this->addPaths($options['paths']);
        }
        unset($options['paths']);

        if (!empty($options['options'])) {
            $options += $options['options'];
            unset($options['options']);
        }
        unset($options['options']);

        // Resolve merged options
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

        $this->state = new State($this->options['state'] ?: null);

        $this->configure();
    }

    /**
     * Gives a chance for extending providers to customize stuff.
     */
    public function configure()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name)
    {
        if (!\array_key_exists($name, $this->options)) {
            throw new \InvalidArgumentException(sprintf('Unknown option "%s"', $name));
        }

        return $this->options[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function addPaths(array $paths)
    {
        $this->paths = array_merge($this->paths, $paths);
    }

    /**
     * {@inheritdoc}
     */
    public function getState(): StateInterface
    {
        if ($this->stateLoaded) {
            return $this->state;
        }

        // lazy-loading for stored states
        try {
            $storedData = $this->storage->fetch($this, State::class, 'state');
        } catch (\Throwable $e) {
            $storedData = null;
        }
        if (null !== $storedData && false !== $storedState = unserialize($storedData)) {
            foreach ($storedState->getAll() as $key => $value) {
                $this->addStateParameter($key, $value);
            }
        }
        $this->stateLoaded = true;

        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function addStateParameter(string $key, string $value): void
    {
        if (!$this->state->has($key)) {
            $this->state->add($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function storeState(StateInterface $state = null): void
    {
        if (null === $state || 0 === \count($state->getAll())) {
            return;
        }

        $this->storage->save($this, $state, 'state');
    }

    /**
     * Retrieve an access token for a given code.
     *
     * @param HttpRequest $request         The request object from where the code is going to extracted
     * @param mixed       $redirectUri     The uri to redirect the client back to
     * @param array       $extraParameters An array of parameters to add to the url
     *
     * @return array array containing the access token and it's 'expires_in' value,
     *               along with any other parameters returned from the authentication
     *               provider
     *
     * @throws AuthenticationException If an OAuth error occurred or no access token is found
     * @throws HttpTransportException
     */
    abstract public function getAccessToken(HttpRequest $request, $redirectUri, array $extraParameters = []);

    /**
     * Refresh an access token using a refresh token.
     *
     * @param string $refreshToken    Refresh token
     * @param array  $extraParameters An array of parameters to add to the url
     *
     * @return array array containing the access token and it's 'expires_in' value,
     *               along with any other parameters returned from the authentication
     *               provider
     *
     * @throws AuthenticationException If an OAuth error occurred or no access token is found
     * @throws HttpTransportException
     */
    public function refreshAccessToken($refreshToken, array $extraParameters = [])
    {
        throw new AuthenticationException('OAuth error: "Method unsupported."');
    }

    /**
     * Revoke an OAuth access token or refresh token.
     *
     * @param string $token the token (access token or a refresh token) that should be revoked
     *
     * @return bool returns True if the revocation was successful, otherwise False
     *
     * @throws AuthenticationException If an OAuth error occurred
     * @throws HttpTransportException
     */
    public function revokeToken($token)
    {
        throw new AuthenticationException('OAuth error: "Method unsupported."');
    }

    /**
     * Get the response object to return.
     *
     * @return UserResponseInterface
     */
    protected function getUserResponse()
    {
        $response = new $this->options['user_response_class']();
        if ($response instanceof PathUserResponse) {
            $response->setPaths($this->paths);
        }

        return $response;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function normalizeUrl($url, array $parameters = [])
    {
        $normalizedUrl = $url;
        if (!empty($parameters)) {
            $normalizedUrl .= (str_contains($url, '?') ? '&' : '?').http_build_query($parameters, '', '&');
        }

        return $normalizedUrl;
    }

    /**
     * Performs an HTTP request.
     *
     * @param string       $url     The url to fetch
     * @param string|array $content The content of the request
     * @param array        $headers The headers of the request
     * @param string       $method  The HTTP method to use
     *
     * @return ResponseInterface The response content
     *
     * @throws HttpTransportException
     */
    protected function httpRequest($url, $content = null, array $headers = [], $method = null)
    {
        if (null === $method) {
            $method = null === $content || '' === $content ? 'GET' : 'POST';
        }

        $options = ['headers' => $headers];
        $options['headers'] += ['User-Agent' => 'HWIOAuthBundle (https://github.com/hwi/HWIOAuthBundle)'];
        if (\is_string($content)) {
            if (!isset($options['headers']['Content-Length'])) {
                $options['headers'] += ['Content-Length' => (string) \strlen($content)];
            }
        }
        $options['body'] = $content;

        try {
            return $this->httpClient->request(
                $method,
                $url,
                $options
            );
        } catch (TransportExceptionInterface $e) {
            throw new HttpTransportException('Error while sending HTTP request', $this->getName(), $e->getCode(), $e);
        }
    }

    protected function getResponseContent(ResponseInterface $rawResponse): array
    {
        try {
            return $rawResponse->toArray(false);
        } catch (JsonException $e) {
            parse_str($rawResponse->getContent(false), $response);

            return $response;
        } catch (TransportExceptionInterface $e) {
            throw new HttpTransportException('Error while sending HTTP request', $this->getName(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $url
     *
     * @return ResponseInterface
     *
     * @throws HttpTransportException
     */
    abstract protected function doGetTokenRequest($url, array $parameters = []);

    /**
     * @param string $url
     *
     * @return ResponseInterface
     *
     * @throws HttpTransportException
     */
    abstract protected function doGetUserInformationRequest($url, array $parameters = []);

    /**
     * Configure the option resolver.
     *
     * @throws AccessException
     * @throws UndefinedOptionsException
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'client_id',
            'client_secret',
            'authorization_url',
            'access_token_url',
            'infos_url',
        ]);

        $resolver->setDefaults([
            'scope' => null,
            'state' => null,
            'csrf' => false,
            'user_response_class' => PathUserResponse::class,
            'auth_with_one_url' => false,
        ]);

        $resolver->setAllowedValues('csrf', [true, false]);
    }
}
