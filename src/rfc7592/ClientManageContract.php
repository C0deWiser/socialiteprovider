<?php

namespace SocialiteProviders\Zenit\rfc7592;

/**
 * @see https://tools.ietf.org/html/rfc7592
 */
interface ClientManageContract
{
    public function getClientConfiguration(): array;

    public function updateClientConfiguration(array $config): array;
}