<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\Response;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @author Joseph Bielawski <stloyd@gmail.com>
 * @author SensioLabs <contact@sensiolabs.com>
 */
class SensioConnectUserResponse extends AbstractUserResponse
{
    /**
     * @var \DOMXpath
     */
    protected $xpath;

    /**
     * @var \DOMElement
     */
    protected $data;

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->data->attributes->getNamedItem('id')->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getNickname()
    {
        $username = null;
        $accounts = $this->xpath->query('./foaf:account/foaf:OnlineAccount', $this->data);
        for ($i = 0; $i < $accounts->length; ++$i) {
            $account = $accounts->item($i);
            if ('SensioLabs Connect' === $this->getNodeValue('./foaf:name', $account)) {
                $username = $this->getNodeValue('foaf:accountName', $account);

                break;
            }
        }

        return $username ?: $this->getNodeValue('./foaf:name', $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getRealName()
    {
        return $this->getNodeValue('./foaf:name', $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->getNodeValue('./foaf:mbox', $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfilePicture()
    {
        return $this->getNodeValue('./atom:link[@rel="foaf:depiction"]', $this->data, 'link');
    }

    /**
     * {@inheritdoc}
     */
    public function setData($data)
    {
        $dom = new \DOMDocument();
        try {
            if (!$dom->loadXML($data)) {
                throw new \ErrorException('Could not transform this xml to a \DOMDocument instance.');
            }
        } catch (\Exception $e) {
            throw new AuthenticationException('Could not retrieve valid user info.');
        }

        $this->xpath = new \DOMXpath($dom);

        $nodes = $this->xpath->evaluate('/api/root');
        $user = $this->xpath->query('./foaf:Person', $nodes->item(0));
        if (1 !== $user->length) {
            throw new AuthenticationException('Could not retrieve user info.');
        }

        $this->data = $user->item(0);
    }

    /**
     * @param string      $query
     * @param \DOMElement $element
     * @param string      $nodeType
     *
     * @return mixed|null
     */
    protected function getNodeValue($query, \DOMElement $element, $nodeType = 'normal')
    {
        $nodeList = $this->xpath->query($query, $element);
        if ($nodeList && $nodeList->length > 0) {
            $node = $nodeList->item(0);
            switch ($nodeType) {
                case 'link':
                    $nodeValue = $node->attributes->getNamedItem('href')->value;
                    break;

                case 'normal':
                default:
                    $nodeValue = $node->nodeValue;
                    break;
            }

            return $this->sanitizeValue($nodeValue);
        }

        return null;
    }

    /**
     * @param string $value
     *
     * @return mixed
     */
    protected function sanitizeValue($value)
    {
        if ('true' === $value) {
            return true;
        }

        if ('false' === $value) {
            return false;
        }

        if (empty($value)) {
            return null;
        }

        return $value;
    }
}
