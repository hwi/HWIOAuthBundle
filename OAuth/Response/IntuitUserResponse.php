<?php


namespace HWI\Bundle\OAuthBundle\OAuth\Response;

/**
 * Description for class IntuitUserResponse
 * @author Volker von Hoesslin <volker.von.hoesslin@empora.com>
 */
class IntuitUserResponse extends PathUserResponse {

    public function setResponse($response) {
        $crawler = new IntuitResponseCrawler($response);
        $user = $crawler->filterXPath('User')->first();
        $response = [
            'identifier' => $user->attr('Id'),
            'nickname' => $user->filterXPath('ScreenName')->text(),
            'firstname' => $user->filterXPath('FirstName')->text(),
            'lastname' => $user->filterXPath('LastName')->text(),
            'realname' => $user->filterXPath('ScreenName')->text(),
            'email' => $user->filterXPath('EmailAddress')->text(),
            'isVerified' => (bool)filter_var($user->filterXPath('IsVerified')->text(), FILTER_VALIDATE_BOOLEAN),
        ];
        parent::setResponse($response);
    }

    /**
     * @return bool
     */
    public function isVerified() {
        return $this->getValueForPath('identifier');
    }

}