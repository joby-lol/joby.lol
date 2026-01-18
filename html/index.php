<?php

use Joby\Leafcutter\CacheControlFactory;
use Joby\Leafcutter\Content\ContentManager;
use Joby\Leafcutter\Content\Handlers\DjotHandler;
use Joby\Leafcutter\Content\Handlers\PassthroughHandler;
use Joby\Leafcutter\Content\Handlers\PhpHandler;
use Joby\Smol\Context\Context;
use Joby\Smol\Request\Request;
use Joby\Smol\Response\Renderer;
use Joby\Smol\Response\Response;
use Joby\Smol\Router\Matchers\CatchallMatcher;
use Joby\Smol\Router\Router;

include('../vendor/autoload.php');

// register various things with the context
// the context is basically a global static dependency injector
Context::register(CacheControlFactory::class);
Context::register(Renderer::class);
Context::register(Router::class);
Context::register(Request::current());
Context::register(ContentManager::class);
Context::register(DjotHandler::class);
Context::register(PassthroughHandler::class);
Context::register(PhpHandler::class);

// set up static content provider
// this is what pulls content from the content directories
$content = Context::get(ContentManager::class);
$content->addFilesystem(__DIR__ . '/../content');
$content->addHandlerClass('djot', DjotHandler::class);
$content->addHandlerClass('dj', DjotHandler::class);
$content->addHandlerClass('php', PhpHandler::class);
$content->addHandlerClass('txt', PassthroughHandler::class);
$content->addHandlerClass('html', PassthroughHandler::class);

// get router instance and configure
$router = Context::get(Router::class);
$router->add(new CatchallMatcher(), $content->route(...));

// build response
$response = Context::get(Router::class)->run(Context::get(Request::class));
Context::register($response);

// render response
Context::get(Renderer::class)->render(Context::get(Response::class));