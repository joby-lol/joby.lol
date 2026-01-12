<?php

namespace Joby\Leafcutter\StaticContent\Handlers;

use Joby\Leafcutter\CacheControlFactory;
use Joby\Leafcutter\StaticContent\StaticContent;
use Joby\Smol\Context\Context;
use Joby\Smol\Context\Invoker\Invoker;
use Joby\Smol\Response\Content\ContentInterface;
use Joby\Smol\Response\Content\StringContent;
use Joby\Smol\Response\Response;
use RuntimeException;
use Stringable;
use Throwable;

class PhpHandler
{

    public static function handle(string $path): Response|null
    {
        $actual_path = StaticContent::actualPath($path);
        if ($actual_path === null)
            return null;
        try {
            $content = Context::get(Invoker::class)->include($actual_path);
            // content may be handled differently depending on the include
            // for Response objects, return them directly
            if ($content instanceof Response) {
                return $content;
            }
            // for ContentInterface objects, wrap them in a Response
            elseif ($content instanceof ContentInterface) {
                return new Response(
                    200,
                    $content,
                    null,
                    Context::get(CacheControlFactory::class)
                        ->publicPage(),
                );
            }
            elseif (is_string($content) || $content instanceof Stringable) {
                // If it's a string, wrap it in a StringContent
                $content = new StringContent((string) $content);
                $content->setFilename(basename($actual_path) . '.html');
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
                throw new RuntimeException("Included PHP file '$actual_path' returned unsupported type: " . gettype($content));
            }
        }
        catch (Throwable $e) {
            throw $e;
        }
    }

}
