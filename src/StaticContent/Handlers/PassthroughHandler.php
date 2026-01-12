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
        $cache_factory = Context::get(CacheControlFactory::class);
        $content = new FileContent($actual_path);
        $type = $content->contentType();
        if ($type === null || !str_starts_with($type, 'text/')) {
            // media and weird files get public media cache control
            $cache = $cache_factory->publicMedia();
        }
        else {
            // text files get public page cache control
            $cache = $cache_factory->publicPage();
        }
        return new Response(
            200,
            $content,
            null,
            $cache,
        );
    }

}
