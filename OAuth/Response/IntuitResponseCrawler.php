<?php


namespace HWI\Bundle\OAuthBundle\OAuth\Response;

use Symfony\Component\DomCrawler\Crawler;


/**
 * Description for class IntuitResponseCrawler
 * @author Volker von Hoesslin <volker.von.hoesslin@empora.com>
 */
class IntuitResponseCrawler extends Crawler {

    const NAMESPACE_PREFIX = 'ir';
    const NAMESPACE_URI = 'http://platform.intuit.com/api/v1';

    public function __construct($node = null, $currentUri = null, $baseHref = null) {
        parent::__construct($node, $currentUri, $baseHref);
        $this->registerNamespace(self::NAMESPACE_PREFIX, self::NAMESPACE_URI);
    }

    /**
     * @param string $xpath
     * @param bool   $setNamespacePrefix
     * @return Crawler
     */
    public function filterXPath($xpath, $setNamespacePrefix = true) {
        $xpath = $setNamespacePrefix ? $this->generateNamespacePrefix($xpath) : $xpath;
        return parent::filterXPath($xpath);
    }

    /**
     * @param $xpath
     * @return string
     */
    protected function generateNamespacePrefix($xpath) {
        return sprintf('//%s:%s', self::NAMESPACE_PREFIX, $xpath);
    }


}