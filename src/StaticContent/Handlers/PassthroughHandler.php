<?php

namespace Joby\Leafcutter\StaticContent\Handlers;

use Joby\Leafcutter\CacheControlFactory;
use Joby\Leafcutter\StaticContent\StaticContent;
use Joby\Smol\Context\Context;
use Joby\Smol\Response\Content\FileContent;
use Joby\Smol\Response\Response;

/**
 * Passes through a direct FileContent response for the requested file.
 */
class PassthroughHandler
{

    public static function handle(string $path): Response|null
    {
        $actual_path = StaticContent::actualPath($path);
        if ($actual_path === null)
            return null;
        return new Response(
            200,
            new FileContent($actual_path),
            null,
            Context::get(CacheControlFactory::class)->publicMedia(),
        );
    }

}
