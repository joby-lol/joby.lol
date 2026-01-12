<?php

use Joby\Leafcutter\CacheControlFactory;
use Joby\Leafcutter\StaticContent\Handlers\PhpHandler;
use Joby\Leafcutter\StaticContent\StaticContent;
use Joby\Leafcutter\StaticContent\Handlers\PassthroughHandler;
use Joby\Smol\Context\Config\DefaultConfig;
use Joby\Smol\Request\Method;
use Joby\Smol\Request\Request;
use Joby\Smol\Response\Renderer;
use Joby\Smol\Response\Response;
use Joby\Smol\Router\Matchers\CatchallMatcher;
use Joby\Smol\Router\Matchers\SuffixMatcher;
use Joby\Smol\Router\Priority;
use Joby\Smol\Router\Router;

include('../vendor/autoload.php');

// register various things with the context
// the context is basically a global static dependency injector
$config = new DefaultConfig();
ctx_register($config);
ctx_register(CacheControlFactory::class);
ctx_register(Renderer::class);
ctx_register(Router::class);
ctx_register(Request::current());

// set up static content provider
// this is what pulls content from the content directories
$content = new StaticContent();
$content->addSourceDirectory(__DIR__ . '/../content');
ctx_register($content);

// get router instance
$router = ctx(Router::class);

// set up matchers/handlers for specific file extensions
// $router->add($content->match(new SuffixMatcher('.md')), MarkdownHandler::handle(...));
// $router->add($content->match(new SuffixMatcher('.html')), HtmlHandler::handle(...));
$router->add($content->match(new SuffixMatcher('.php')), PhpHandler::handle(...));

// fallback content matcher to just pass through media files
$router->add($content->match(new CatchallMatcher), PassthroughHandler::handle(...), Method::GET, Priority::LOW);

// build response
$response = ctx(Router::class)->run(ctx(Request::class));
ctx_register($response);

// render response
ctx(Renderer::class)->render(ctx(Response::class));
