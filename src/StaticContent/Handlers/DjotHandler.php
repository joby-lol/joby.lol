<?php

namespace Joby\Leafcutter\StaticContent\Handlers;

use Djot\DjotConverter;
use Joby\Leafcutter\CacheControlFactory;
use Joby\Leafcutter\StaticContent\StaticContent;
use Joby\Smol\Context\Context;
use Joby\Smol\Response\Content\StringContent;
use Joby\Smol\Response\Response;
use RuntimeException;

class DjotHandler
{

    public static function handle(string $path): Response|null
    {
        $path = StaticContent::actualPath($path);
        if ($path === null)
            throw new RuntimeException("File not found: $path");
        $content = file_get_contents($path);
        if ($content === false)
            throw new RuntimeException("Failed to read file: $path");
        $content = static::converter()->convert($content);
        $content = new StringContent($content);
        $content->setFilename(basename($path) . '.html');
        return new Response(
            200,
            $content,
            null,
            Context::get(CacheControlFactory::class)->publicPage(),
        );
    }

    public static function converter(): DjotConverter
    {
        /** @var DjotConverter|null */
        static $converter = null;
        if ($converter === null) {
            $converter = new DjotConverter();
        }
        return $converter;
    }

}
