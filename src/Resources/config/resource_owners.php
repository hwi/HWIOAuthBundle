<?php

declare(strict_types=1);

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner as RO;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMapLocator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('hwi_oauth.resource_owner.oauth1.class', RO\GenericOAuth1ResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.oauth2.class', RO\GenericOAuth2ResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.amazon.class', RO\AmazonResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.apple.class', RO\AppleResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.asana.class', RO\AsanaResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.auth0.class', RO\Auth0ResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.azure.class', RO\AzureResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.bitbucket.class', RO\BitbucketResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.bitbucket2.class', RO\Bitbucket2ResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.bitly.class', RO\BitlyResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.box.class', RO\BoxResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.bufferapp.class', RO\BufferAppResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.clever.class', RO\CleverResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.dailymotion.class', RO\DailymotionResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.deviantart.class', RO\DeviantartResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.deezer.class', RO\DeezerResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.discogs.class', RO\DiscogsResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.disqus.class', RO\DisqusResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.dropbox.class', RO\DropboxResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.eve_online.class', RO\EveOnlineResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.eventbrite.class', RO\EventbriteResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.facebook.class', RO\FacebookResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.fiware.class', RO\FiwareResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.flickr.class', RO\FlickrResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.foursquare.class', RO\FoursquareResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.genius.class', RO\GeniusResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.github.class', RO\GitHubResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.gitlab.class', RO\GitLabResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.google.class', RO\GoogleResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.youtube.class', RO\YoutubeResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.hubic.class', RO\HubicResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.instagram.class', RO\InstagramResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.jawbone.class', RO\JawboneResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.jira.class', RO\JiraResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.keycloak.class', RO\KeycloakResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.linkedin.class', RO\LinkedinResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.mailru.class', RO\MailRuResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.office365.class', RO\Office365ResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.paypal.class', RO\PaypalResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.qq.class', RO\QQResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.reddit.class', RO\RedditResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.runkeeper.class', RO\RunKeeperResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.salesforce.class', RO\SalesforceResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.sensio_connect.class', RO\SensioConnectResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.sina_weibo.class', RO\SinaWeiboResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.slack.class', RO\SlackResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.spotify.class', RO\SpotifyResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.soundcloud.class', RO\SoundcloudResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.stack_exchange.class', RO\StackExchangeResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.stereomood.class', RO\StereomoodResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.strava.class', RO\StravaResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.toshl.class', RO\ToshlResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.trakt.class', RO\TraktResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.trello.class', RO\TrelloResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.twitch.class', RO\TwitchResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.twitter.class', RO\TwitterResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.vkontakte.class', RO\VkontakteResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.windows_live.class', RO\WindowsLiveResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.wordpress.class', RO\WordpressResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.xing.class', RO\XingResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.yahoo.class', RO\YahooResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.yandex.class', RO\YandexResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.odnoklassniki.class', RO\OdnoklassnikiResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.37signals.class', RO\ThirtySevenSignalsResourceOwner::class);
    $parameters->set('hwi_oauth.resource_owner.itembase.class', RO\ItembaseResourceOwner::class);

    $services = $containerConfigurator->services();

    $services->set('hwi_oauth.abstract_resource_ownermap', ResourceOwnerMap::class)
        ->abstract()
        ->arg('$httpUtils', service('security.http_utils'))
        ->arg('$possibleResourceOwners', '%hwi_oauth.resource_owners%')
        ->arg('$resourceOwners', [])
        ->arg('$locator', tagged_locator('hwi_oauth.resource_owner', 'resource-name', 'getDefaultResourceNameName', 'getDefaultResourceNamePriority'));

    $services->set('hwi_oauth.resource_ownermap_locator', ResourceOwnerMapLocator::class);
};
