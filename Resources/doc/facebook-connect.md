Facebook Connect
===========================

This guide use Symfony 2.2 and use the default AcmeBundle as base. Assuming you have installed the bundle via Composer correctly.

## Configuration

### Configurating the Resource Owner

```yaml
# app/config/config.yml

hwi_oauth:
    firewall_name: secured_area
    resource_owners:
        facebook:
            type:          facebook
            client_id:     12345678910
            client_secret: asdfg8d77b5ac56804b89e9be31d161e
            scope:         "email"

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
    pattern: /demo/secured/login_facebook
```

### Configurating the Security Layer

```yaml
# app/config/security.yml

providers:
    oauth_user_provider:
        id: hwi_oauth.user.provider.entity

firewalls:
    secured_area:
        pattern:    ^/demo/secured/
        oauth:
            resource_owners:
                facebook:      /demo/secured/login_facebook
            login_path:        /demo/secured/login
            failure_path:      /demo/secured/login
            oauth_user_provider:
                service: hwi_oauth.user.provider.entity
    # Turn on anonymous for testing's need.
    anonymous: true
```

## Login button

```twig
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
                    });
                }
            });
        }
    </script>

    <h1 class="title">Hello {{ name }}!</h1>

    <a href="{{ path('_demo_secured_hello_admin', { 'name': name }) }}">Hello resource secured for <strong>admin</strong> only.</a>

    <p>
        <a href="#" onclick="fb_login();">Facebook Connect Button (Dialog)</a>
    </p>

    {% render(controller('HWIOAuthBundle:Connect:connect')) %}
{% endblock %}
```

Browse to `/demo/secured/hello/World` and test the login button.