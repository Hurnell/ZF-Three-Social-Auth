<?php

namespace ZfThreeSocialAuth\Providers\ProviderInterface;

interface ProviderInterface
{

    public function getRedirectRoute($callback);

    public function sendClientRequest($callback, $queryParams);
}
