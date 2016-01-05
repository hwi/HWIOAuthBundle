<?php
namespace HWI\Bundle\OAuthBundle;

final class HWIOAuthUserEvents
{
    const REGISTRATION_INIT = 'hwi_oauth.registration.initialize';

    const REGISTRATION_SUCCESS = 'hwi_oauth.registration.success';

    const REGISTRATION_COMPLETED = 'hwi_oauth.registration.completed';

    const CONNECT_INIT = 'hwi_oauth.connect.initialize';

    const CONNECT_CONFIRMED = 'hwi_oauth.connect.confirmed';

    const CONNECT_COMPLETED = 'hwi_oauth.connect.completed';
}