Step 4: Integrating with FOSUserBundle
======================================

If you already use FOSUserBundle & you would like to integrate different OAuth 1.0a / OAuth2 services,
with help of this article you will learn how to achieve this.

We of course consider that you have successfully configured some providers (e.g [Adding "Facebook Connect" functionality](bonus/facebook-connect.md)).

### 1) Add resource properties for User class

Update your User entity class, by adding properties for storing provider user ID & access token. It may looks like:

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
    private $facebookId;

    private $facebookAccessToken;

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
        $this->facebookId = $facebookId;

        return $this;
    }

    /**
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * @param string $facebookAccessToken
     * @return User
     */
    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebookAccessToken = $facebookAccessToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getFacebookAccessToken()
    {
        return $this->facebookAccessToken;
    }
}
```
After adding extra properties to User entity, you need to extend base FOSUBUserProvider.

### 2) Extend default FOSUBUserProvider

The bundle provide bridge class for connect FOSUserBundle User class and HWIOAuth out of the box.
You should extend it if you want to add more advanced behavior. For example out of the box it allows to find user by response token
, but doesn't allow create it.

In `MyBundle\Security\Core\User` create class, lets call it `MyFOSUBUserProvider`:

```php
namespace MyBundle\Security\Core\User;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseFOSUBProvider;
use Symfony\Component\Security\Core\User\UserInterface;

class MyFOSUBUserProvider extends BaseFOSUBProvider
{
    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        // get property from provider configuration by provider name
        // , it will return `facebook_id` in that case (see service definition below)
        $property = $this->getProperty($response);
        $username = $response->getUsername(); // get the unique user identifier

        //we "disconnect" previously connected users
        $existingUser = $this->userManager->findUserBy(array($property => $username));
        if (null !== $existingUser) {
            // set current user id and token to null for disconnect
            // ...

            $this->userManager->updateUser($existingUser);
        }
        // we connect current user, set current user id and token
        // ...
        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $userEmail = $response->getEmail();
        $user = $this->userManager->findUserByEmail($userEmail);

        // if null just create new user and set it properties
        if (null === $user) {
            $username = $response->getRealName();
            $user = new User();
            $user->setUsername($username);

            // ... save user to database

            return $user;
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

Append the following lines to `app/config/config.yml`:

```yml
services:
    my.custom.user_provider:
        class:        MyBundle\Security\Core\User\MyFOSUBUserProvider
        arguments: ['@fos_user.user_manager', { facebook: facebook_id }]
```

### 4) Additional configuration for HWIOAuthBundle

Add your service for provider as `account_connector` to configuration, and `fosub` section:

```yml
hwi_oauth:
    connect:
        account_connector: my.custom.user_provider
    firewall_names:
        - 'hwi_oauth_firewall_name' # name of security firewall configured to work with HWIOAuthBundle
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
