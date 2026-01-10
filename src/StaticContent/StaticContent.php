<?php

namespace Joby\Leafcutter\StaticContent;

use Joby\Smol\Router\MatcherInterface;
use Joby\Smol\Router\Matchers\TransformerMatcher;
use RuntimeException;

/**
 * Class for working with the site content directories, including looking up whether given paths exist, and matching/transforming them to actual paths that definitely exist.
 */
class StaticContent
{

    /** @var array<string> directories where files should be looked for, normalized to include a trailing slash */
    protected static array $source_directories = [];

    /** @var array<string> extensions that can be used for index files */
    public static array $index_extensions = ['md', 'html', 'php'];

    /** @var array<string,string|null> cached matchPath() return values */
    public static array $cache = [];

    public static function match(MatcherInterface $matcher): TransformerMatcher
    {
        return new TransformerMatcher(
            self::matchPath(...),
            'original_path',
            $matcher,
        );
    }

    public static function addSourceDirectory(string $directory): void
    {
        $realpath = realpath($directory);
        if ($realpath === false)
            throw new RuntimeException("Source directory $directory not found");
        if (!is_dir($realpath))
            throw new RuntimeException("Source directory $realpath is not a directory");
        if (!is_readable($realpath))
            throw new RuntimeException("Source directory $realpath is not readable");
        $realpath .= "/";
        self::$source_directories[] = $realpath;
    }

    public static function removeSourceDirectory(string $directory): void
    {
        $realpath = realpath($directory);
        if ($realpath === false)
            throw new RuntimeException("Source directory $directory not found");
        $realpath .= "/";
        self::$source_directories = array_diff(self::$source_directories, [$realpath]);
    }

    /**
     * Match a given request path to a canonical path. Will return a string if the given path exists in a source directory. The returned path will have a matching index filename appended if necessary to create a specific filename.
     * 
     * Returned path will be relative to the web root (i.e. relative to the source directory) and not contain any leading or trailing slashes. It will always contain a trailing filename if it is not null.
     * 
     * Rules and edge cases:
     * 
     * Input path leading and trailing slashes are stripped and ignored.
     * 
     * A source file like foo/bar.html/index.html can match requests for foo/bar.html, but only if foo/bar.html doesn't exist in any source directories.
     * 
     * Source directory precedence is the same as their order internally. Later-added source directories are lower priority.
     */
    public static function matchPath(string $path): string|null
    {
        if (!array_key_exists($path, self::$cache)) {
            self::$cache[$path] = self::doMatchPath($path);
        }
        return self::$cache[$path];
    }

    public static function actualPath(string $path): string|null
    {
        $path = static::normalizePath($path);
        if ($path === null)
            return null;
        // look for files in each directory
        foreach (self::$source_directories as $source_directory) {
            if (is_file($source_directory . $path)) {
                return $source_directory . $path;
            }
        }
        // return null if nothing found
        return null;
    }

    protected static function doMatchPath(string $path): string|null
    {
        $path = static::normalizePath($path);
        if ($path === null)
            return null;
        // first look for exact matches, only if there is a file extension
        if (pathinfo($path, PATHINFO_EXTENSION)) {
            foreach (static::$source_directories as $source_directory) {
                if (is_file($source_directory . $path)) {
                    return $path;
                }
            }
        }
        // next look for index.* files
        foreach (static::$source_directories as $source_directory) {
            foreach (static::$index_extensions as $extension) {
                $candidate = $path . '/index.' . $extension;
                if (is_file($source_directory . $candidate)) {
                    return $candidate;
                }
            }
        }
        // return null if nothing was found
        return null;
    }

    public static function normalizePath(string $path): string|null
    {
        $path = trim($path, '/');
        $path = str_replace('\\', '/', $path);
        // check for anything untoward in the path and just return null
        if (str_contains($path, '..')) {
            return null;
        }
        if (str_starts_with($path, '.')) {
            return null;
        }
        if (str_ends_with($path, '.')) {
            return null;
        }
        if (str_starts_with($path, '_')) {
            return null;
        }
        if (str_contains($path, '/_')) {
            return null;
        }
        if (str_contains($path, '/.')) {
            return null;
        }
        return $path;
    }

}
