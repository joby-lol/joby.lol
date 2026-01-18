<?php

namespace Joby\Leafcutter\Content\Handlers;

use Joby\Leafcutter\CacheControlFactory;
use Joby\Leafcutter\Content\ContentHandlerInterface;
use Joby\Leafcutter\Content\ContentManager;
use Joby\Smol\Context\Context;
use Joby\Smol\Filesystem\File;
use Joby\Smol\Response\Content\ContentInterface;
use Joby\Smol\Response\Content\StringContent;
use Joby\Smol\Response\Response;
use RuntimeException;
use Stringable;
use Throwable;

class PhpHandler implements ContentHandlerInterface
{

    public function __construct(
        protected ContentManager $content,
    ) {}

    public function handle(File $file): Response|null
    {
        try {
            $content = Context::include($file->path);
            // content may be handled differently depending on the include
            // for Response objects, return them directly
            if ($content instanceof Response) {
                return $content;
            }
            // for ContentInterface objects, wrap them in a Response
            elseif ($content instanceof ContentInterface) {
                $cache_factory = Context::get(CacheControlFactory::class);
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
            elseif (is_string($content) || $content instanceof Stringable) {
                // If it's a string, wrap it in a StringContent and assume it's HTML
                $content = new StringContent((string) $content);
                $content->setFilename($file->filename() . '.html');
                return new Response(
                    200,
                    $content,
                    null,
                    Context::get(CacheControlFactory::class)
                        ->publicPage(),
                );
            }
            else {
                // unknown return type
                throw new RuntimeException("Included PHP file '$file' returned unsupported type: " . gettype($content));
            }
        }
        catch (Throwable $e) {
            throw $e;
        }
    }

}
