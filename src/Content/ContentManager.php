<?php

namespace Joby\Leafcutter\Content;

use Joby\Smol\Context\Context;
use Joby\Smol\Filesystem\Filesystem;
use Joby\Smol\Response\Response;

/**
 * Class for working with the site content directories, including looking up whether given paths exist, and matching/transforming them to actual paths that definitely exist.
 */
class ContentManager
{

    /** 
     * An array of filesystems representing content directories.
     * 
     * @var array<Filesystem> $filesystems 
     */
    protected array $filesystems = [];

    /** 
     * An array of file extension to content handler mappings.
     * 
     * @var array<string,class-string<ContentHandlerInterface>> $handlers 
     */
    protected array $handlers = [];

    /**
     * Handle a request for a given path, returning either a built Response or null if no content exists at that path.
     */
    public function route(string $path): Response|null
    {
        if ($path === '')
            $glob = 'index.*';
        else
            $glob = "{{$path},{$path}/index.*}";
        foreach ($this->filesystems as $fs) {
            $files = $fs->files($glob);
            if (empty($files))
                continue;
            $file = $files[0];
            $ext = $file->extension();
            if (!isset($this->handlers[$ext]))
                continue;
            return Context::get($this->handlers[$ext])
                ->handle($file);
        }
        return null;
    }

    public function addFilesystem(Filesystem|string $fs): static
    {
        if (is_string($fs))
            $fs = new Filesystem($fs);
        $this->filesystems[] = $fs;
        return $this;
    }

    /**
     * Register a content handler class for a given file extension.
     * 
     * @param string $extension The file extension (without leading dot).
     * @param class-string<ContentHandlerInterface> $handler_class The content handler class to register.
     */
    public function addHandlerClass(string $extension, string $handler_class): static
    {
        $this->handlers[$extension] = $handler_class;
        return $this;
    }

}
