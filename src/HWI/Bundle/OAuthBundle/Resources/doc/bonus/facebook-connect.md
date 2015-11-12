Bonus: Facebook Connect
=======================
This guide bases on Symfony 2.1+ and the [AcmeDemoBundle](https://github.com/symfony/symfony-standard/tree/2.2).

## Configuration

### Configuration of the Resource Owner

```yaml
# app/config/config.yml

hwi_oauth:
    firewall_names:        [secured_area]
    resource_owners:
        facebook:
            type:          facebook
            client_id:     <client_id>
            client_secret: <client_secret>
            scope:         "email"
            options:
                display: popup #dialog is optimized for popup window

services:
    hwi_oauth.user.provider.entity:
        class: HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider
```

### Import Routing

```yaml
# app/config/routing.yml

hwi_oauth_redirect:
    resource: "@HWIOAuthBundle/Resources/config/routing/redirect.xml"
    prefix:   /demo/secured/connect

facebook_login:
    path: /demo/secured/login_facebook
```

### Configuration of the Security Layer

```yaml
# app/config/security.yml

firewalls:
    # ...
    secured_area:
        pattern:    ^/demo/secured/
        oauth:
            resource_owners:
                facebook:      /demo/secured/login_facebook
            login_path:        /demo/secured/login
            failure_path:      /demo/secured/login
            oauth_user_provider:
                service: hwi_oauth.user.provider.entity

        # Turn on anonymous for testings need.
        anonymous: ~
```

## Adding the Facebook Login Button

The following example bases also on the Facebook ["Login with Javascript SDK"](https://developers.facebook.com/docs/howtos/login/getting-started/) guide.

```html+jinja
# src/Acme/DemoBundle/Resources/views/Secured/hello.html.twig

{% block content %}
    <div id="fb-root"></div>
    <script>
        window.fbAsyncInit = function() {
            // init the FB JS SDK
            FB.init({
                appId      : '12345678910',                        // App ID from the app dashboard
                channelUrl : '//yourdomain.com/channel.html',      // Channel file for x-domain comms
                status     : true,                                 // Check Facebook Login status
                xfbml      : true                                  // Look for social plugins on the page
            });
        };

        // Load the SDK asynchronously
        (function(d, s, id){
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/en_US/all.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));

        function fb_login() {
            FB.getLoginStatus(function(response) {
                if (response.status === 'connected') {
                    // connected
                    alert('Already connected, redirect to login page to create token.');
                    document.location = "{{ url("hwi_oauth_service_redirect", {service: "facebook"}) }}";
                } else {
                    // not_authorized
                    FB.login(function(response) {
                        if (response.authResponse) {
                            document.location = "{{ url("hwi_oauth_service_redirect", {service: "facebook"}) }}";
                        } else {
                            alert('Cancelled.');
                        }
                    }, {scope: 'email'});
                }
            });
        }
    </script>

    <h1 class="title">Hello {{ name }}!</h1>

    <a href="{{ path('_demo_secured_hello_admin', { 'name': name }) }}">Hello resource secured for <strong>admin</strong> only.</a>

    <p>
        <a href="#" onclick="fb_login();">Facebook Connect Button (Dialog)</a>
    </p>

    {# Bonus: Show all available login link in HWIOAuthBundle #}
    {% render(controller('HWIOAuthBundle:Connect:connect')) %}
{% endblock %}
```

Make sure `{scope: 'email'}` is added as the second argument to FB.login. Or elsewhere, you would have to prompt the user with the authentication for the basic data, and then ask him again to accept that you need his email.

## Watch the results!

Open the browser and go at `/demo/secured/hello/World` too see the login button, and test it!

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md)
