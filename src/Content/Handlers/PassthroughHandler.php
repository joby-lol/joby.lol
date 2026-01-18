<?php

namespace Joby\Leafcutter\Content\Handlers;

use Joby\Leafcutter\CacheControlFactory;
use Joby\Leafcutter\Content\ContentHandlerInterface;
use Joby\Leafcutter\Content\ContentManager;
use Joby\Smol\Context\Context;
use Joby\Smol\Filesystem\File;
use Joby\Smol\Response\Content\FileContent;
use Joby\Smol\Response\Response;

/**
 * Passes through a direct FileContent response for the requested file.
 */
class PassthroughHandler implements ContentHandlerInterface
{

    public function __construct(
        protected ContentManager $content,
    ) {}

    public function handle(File $file): Response|null
    {
        $cache_factory = Context::get(CacheControlFactory::class);
        $content = new FileContent($file->path);
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
