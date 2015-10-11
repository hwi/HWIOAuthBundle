Step 4: Integrating with FOSUserBundle
======================================

If you use FOSUserBundle and you want to integrate it with HWIOAuthBundle this article will describe most mahor
FOSUserBundle is the most popular Symfony2 bundle, and when you use HWIOAuthBundle for allow to users to authenticate
through different networks and services you obviously want to integrate you User class with HWIOauthBundle.

Consider that you have been successfully configure any provider (e.g [Adding "Facebook Connect" functionality](bonus/facebook-connect.md))

### 1) Add resource properties for User class

Update your User class, by add properties for storing provider user id and access token. It may looks like:

```php
namespace MyBundle\Entity;

use FOS\UserBundle\Model\User as FOSUBUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity
 */
class User extends FOSUBUser
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="facebook_id", type="string", length=255, nullable=true)
     */
    private $facebook_id;

    /**
     * @ORM\Column(name="facebook_access_token", type="string", length=255, nullable=true)
     */
    private $facebook_access_token;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $facebookId
     * @return User
     */
    public function setFacebookId($facebookId)
    {
        $this->facebook_id = $facebookId;

        return $this;
    }

    /**
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebook_id;
    }

    /**
     * @param string $facebookAccessToken
     * @return User
     */
    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebook_access_token = $facebookAccessToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getFacebookAccessToken()
    {
        return $this->facebook_access_token;
    }
```
After adding extra properties to User entity, you need to extend base FOSUBUserProvider (if you want to add more advanced behavior, than provided from the box)


### 2) Extend default FOSUBUserProvider

The bundle provide bridge class for connect FOSUserBundle User class and HWIOAuth out of the box.
You should extend it if you want to add more advanced behavior.

In `YourBundle\Security\Core\User` create class, lets call it `MyFOSUBUserProvider`:

```php

namespace MyBundle\Security\Core\User;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseFOSUBProvider;
use Symfony\Component\Security\Core\User\UserInterface;

class FOSUBUserProvider extends BaseFOSUBProvider
{
    /**
     * {@inheritDoc}
     */
    public function __construct(UserManagerInterface $userManager, array $properties)
    {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $username = $response->getUsername();
        $service = $response->getResourceOwner()->getName(); //get name of resource owner

        //we "disconnect" previously connected users
        $previousUser = $this->userManager->findUserBy([$property => $username])
        if (null !== $previousUser) {
            // set current user id and token to null for disconect
            ...
            $this->userManager->updateUser($previousUser);
        }
        //we connect current user, set current user id and token
        ...
        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username = $response->getRealName();
        $userEmail = $response->getEmail();
        $user = $this->userManager->findUserByEmail($userEmail);
        if (null === $user) {
            // if null just create new user and set it properties

            return $user
        }
        // else update access token of existing user
        $serviceName = $response->getResourceOwner()->getName();
        $setter = 'set' . ucfirst($serviceName) . 'AccessToken';
        $user->$setter($response->getAccessToken());//update access token

        return $user;
    }
}

```

### 3) Configure user provider as service

To `MyBundle\Resources\config\services.xml`, add following code for declare your custom provider as service

```xml
<service id="my.custom.user_provider" class="MyBundle\Security\Core\User\FOSUBUserProvider">
    <argument type="service" id="fos_user.user_manager"/>
    <argument type="collection">
        <argument key="facebook">facebook_id</argument>
    </argument>
</service>

```

### 4) Additional configuration for HWIOAuthBundle

Add your service for provider as `account_connector` to configuration, and `fosub` section:

```yml
hwi_oauth:
    connect:
        account_connector: my.custom.user_provider
    firewall_name: main
    http_client:
        verify_peer: false
    fosub:
        username_iterations: 30
        properties:
            facebook:    facebook_id
    resource_owners:
        facebook:
            type:                facebook
            client_id:           id
            client_secret:       secret
            scope:               scope
```
