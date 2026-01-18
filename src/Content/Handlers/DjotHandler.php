<?php

namespace Joby\Leafcutter\Content\Handlers;

use Djot\DjotConverter;
use Joby\Leafcutter\CacheControlFactory;
use Joby\Leafcutter\Content\ContentHandlerInterface;
use Joby\Leafcutter\Content\ContentManager;
use Joby\Smol\Context\Context;
use Joby\Smol\Filesystem\File;
use Joby\Smol\Response\Content\StringContent;
use Joby\Smol\Response\Response;
use RuntimeException;

class DjotHandler implements ContentHandlerInterface
{

    public function __construct(
        protected ContentManager $content,
    ) {}

    public function handle(File $file): Response|null
    {
        $content = $file->read();
        if ($content === false)
            throw new RuntimeException("Failed to read content from file: $file");
        $content = static::converter()->convert($content);
        $content = new StringContent($content);
        $content->setFilename($file->filename() . '.html');
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
