<?php

namespace Joby\Leafcutter\Content;

use Joby\Smol\Filesystem\File;
use Joby\Smol\Response\Response;

interface ContentHandlerInterface
{

    public function handle(File $file): Response|null;

}
