<?php

namespace SocialiteProviders\Zenit;

use SocialiteProviders\Manager\SocialiteWasCalled;

class ZenitExtendSocialite
{
    /**
     * Register the provider.
     *
     * @param SocialiteWasCalled $socialiteWasCalled
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('zenit', ZenitProvider::class);
    }
}