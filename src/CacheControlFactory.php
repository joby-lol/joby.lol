<?php

namespace Joby\Leafcutter;

use Joby\Smol\Context\Invoker\ConfigValue;
use Joby\Smol\Response\CacheControl;

class CacheControlFactory
{

    public function __construct(
        #[ConfigValue("cache.page_ttl")]
        public int $page_ttl = 300,
        #[ConfigValue("cache.page_stale_ttl")]
        public int $page_stale_ttl = 86400,
        #[ConfigValue("cache.media_ttl")]
        public int $media_ttl = 86400,
        #[ConfigValue("cache.media_stale_ttl")]
        public int $media_stale_ttl = 86400 * 30,
    ) {}

    public function publicPage(): CacheControl
    {
        return new CacheControl(
            false,
            true,
            false,
            $this->page_ttl,
            $this->page_ttl,
            $this->page_stale_ttl,
            $this->page_stale_ttl,
        );
    }

    public function publicMedia(): CacheControl
    {
        return new CacheControl(
            false,
            true,
            false,
            $this->media_ttl,
            $this->media_ttl,
            $this->media_stale_ttl,
            $this->media_stale_ttl,
        );
    }

    public function privatePage(): CacheControl
    {
        return new CacheControl(
            false,
            false,
            true,
            $this->page_ttl,
            $this->page_ttl,
            $this->page_ttl,
            $this->page_stale_ttl,
        );
    }

    public function privateMedia(): CacheControl
    {
        return new CacheControl(
            false,
            false,
            true,
            $this->media_ttl,
            $this->media_ttl,
            $this->media_ttl,
            $this->media_stale_ttl,
        );
    }

}
