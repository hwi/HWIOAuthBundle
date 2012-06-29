Playing with HWIOAuthBundle token roles
=======================================
When using HWIOAuthBundle, every signed-in user gets two additional security roles:

* `ROLE_HWI_OAUTH_USER`
* `ROLE_HWI_OAUTH_RESOURCE_NAME`

Where `RESOURCE_NAME` is one of the supported resource owners:

* Facebook
* GitHub
* Google
* LinkedIn
* Sensio Connect
* Twitter
* Vkontakte
* Windows Live

With those roles you can simply make some part of application visible/accessible
for one (or all using OAuth) resource owner, i.e.:

``` php
<?php

// (...)

class ProfileController extends Controller
{
    // (...)

    public function listGithubCommitsAction()
    {
        if (!$this->container->get('security.context')->isGranted('ROLE_HWI_OAUTH_GITHUB')) {
            return $this->redirect('homepage');
        }

        // (...)
    }

    // (...)
}
```
