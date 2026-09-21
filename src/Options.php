<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub;

/**
 * The address the gateway is reached at, the credentials it is reached with
 * and the channel the caller speaks for. A credential pair belongs to a
 * single team, and the team is part of the address, so a pair only ever
 * opens its own team's endpoints.
 */
final readonly class Options
{
    /**
     * The header the API key travels in.
     */
    public const API_KEY_HEADER = 'X-Api-Key';

    public function __construct(
        /** The address the application is served from, e.g. https://odeme.gurmehub.com. */
        public string $baseUrl,
        /** The slug of the team the payments are made on behalf of. */
        public string $team,
        /**
         * The channel every request speaks for: the shop, the marketplace or
         * the branch the customer reached the merchant through, by the
         * number the team's own Kanallar page gives it. It belongs to the
         * integration rather than to any one payment, so it is named once
         * here; a merchant selling on more than one channel may still name
         * another on a single request.
         */
        public int $channelId,
        public string $apiKey,
        public string $apiSecret,
    ) {}

    /**
     * The full address of a gateway endpoint for this team.
     */
    public function url(string $path): string
    {
        return rtrim($this->baseUrl, '/').'/api/'.$this->team.'/gateway/'.$path;
    }
}
