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
        $content = new FileContent($actual_path);
        return new Response(
            200,
            $content,
            null,
            str_starts_with($content->contentType(), 'text/')
            ? Context::get(CacheControlFactory::class)->publicPage()
            : Context::get(CacheControlFactory::class)->publicMedia(),
        );
    }

}
