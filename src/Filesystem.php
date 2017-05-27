<?php
/**
 * Part of the Laradic PHP Packages.
 *
 * Copyright (c) 2017. Robin Radic.
 *
 * The license can be found in the package and online at https://laradic.mit-license.org.
 *
 * @copyright Copyright 2017 (c) Robin Radic
 * @license https://laradic.mit-license.org The MIT License
 */
namespace Laradic\Filesystem;

use BadMethodCallException;
use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Laradic\Support\Arr;

use Laradic\Support\Path;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;


/**
 * Laradic Support Filesystem
 *
 * @author    Laradic Dev Team
 * @copyright Copyright (c) 2015, Laradic
 * @license   https://tldrlegal.com/license/mit-license MIT License
 * @package   Laradic\Support
 *
 * @mixin \Illuminate\Filesystem\Filesystem
 * @mixin \Symfony\Component\Filesystem\Filesystem
 */
class Filesystem extends IlluminateFilesystem
{

    const GLOB_ROOTFIRST  = 32768;
    const GLOB_CHILDFIRST = 65536;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $symfony;

    protected $illuminate;

    /**
     * @var Temp
     */
    protected static $tempClass = Temp::class;

    /**
     * @return mixed
     */
    public static function getTempClass()
    {
        return self::$tempClass;
    }

    /**
     * Set the tempDirClass value
     *
     * @param mixed $tempClass
     * @return Filesystem
     */
    public static function setTempClass($tempClass)
    {
        self::$tempClass = $tempClass;
    }

    /**
     * createTempDir method
     * @param string $prefix
     * @return Temp
     */
    public function createTemp($prefix = '')
    {
        $tempClass = self::$tempClass;
        return new $tempClass($prefix);
    }

    public function ensureDirectory($path, $recursive = true, $mode = 0755, $force = true)
    {
        if ( is_array($path) ) {
            foreach ( $path as $p ) {
                $this->ensureDirectory($p, $recursive, $mode, $force);
            }
        } elseif ( !$this->exists($path) ) {
            $this->makeDirectory($path, $mode, $recursive, $force);
            #if ( $recursive === true && $this->exists($path = Path::getDirectory($path)) ) {}
        }
    }


    /**
     * Create a new filesystem instance.
     *
     */
    public function __construct()
    {
        $this->symfony    = new SymfonyFilesystem;
        $this->illuminate = new IlluminateFilesystem;
    }

    /**
     * create method
     *
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Recursively find pathnames matching the given pattern, uses braces.
     *
     * @param $pattern
     *
     * @return array
     */
    public function globule($pattern)
    {
        return $this->rglob($pattern, GLOB_BRACE);
    }

    /**
     * Search the given folder recursively for files using
     * a regular expression pattern.
     *
     * @param  string $folder
     * @param  string $pattern
     *
     * @return array
     */
    public function search($folder, $pattern)
    {
        $dir      = new RecursiveDirectoryIterator($folder);
        $iterator = new RecursiveIteratorIterator($dir);
        $files    = new RegexIterator($iterator, $pattern, RegexIterator::GET_MATCH);
        $fileList = [ ];

        foreach ( $files as $file )
        {
            $fileList = array_merge($fileList, $file);
        }


        return $fileList;
    }

    /**
     * Search the given folder recursively for files using
     * a regular expression pattern.
     *
     * @deprecated Use the search method instead
     *
     * @param $folder
     * @param $pattern
     *
     * @return array
     */
    public function rsearch($folder, $pattern)
    {
        return $this->search($folder, $pattern);
    }

    /**
     * Extended `glob()` functionality that supports double star `**` (globstar) wildcard.
     *
     * PHP's `glob()` implementation doesn't allow for `**` wildcard. In Bash 4 it can be enabled with `globstar` setting.
     *
     * In case the `**` wildcard is not used in the pattern then this method just calls PHP's `glob()`.
     *
     * For full documentation see PHP's [`glob()` documentation](http://php.net/manual/en/function.glob.php).
     *
     * @package    Foundation
     * @subpackage Utils
     * @author     Michał Dudek <michal@michaldudek.pl>
     * @see        http://www.michaldudek.pl/Foundation/MD/Foundation/Utils/FilesystemUtils.html
     *
     * @copyright  Copyright (c) 2013, Michał Dudek
     * @license    MIT
     *
     * @param  string $pattern The pattern. Supports `**` wildcard.
     * @param  int    $flags   [optional] `glob()` flags. See `glob()`'s documentation. Default: `0`.
     *
     * @return array|bool
     */
    public function rglob($pattern, $flags = 0)
    {
        $flags = $flags | GLOB_BRACE;
        // if not using ** then just use PHP's glob()
        if ( stripos($pattern, '**') === false )
        {
            // turn off the custom flags
            $files = glob($pattern, ($flags | static::GLOB_CHILDFIRST | static::GLOB_ROOTFIRST) ^ (static::GLOB_CHILDFIRST | static::GLOB_ROOTFIRST));
            // sort by root first?
            if ( $flags & static::GLOB_ROOTFIRST )
            {
                $files = Arr::sortPaths($files, true);
            }
            else
            {
                if ( $flags & static::GLOB_CHILDFIRST )
                {
                    $files = Arr::sortPaths($files, false);
                }
                else
                {
                    // default sort order is alphabetically
                    sort($files);
                }
            }

            return $files;
        }
        $patterns = [ ];
        // if globstar is inside braces
        if ( $flags & GLOB_BRACE )
        {
            $regexp = '/\{(.+)?([\*]{2}[^,]?)(.?)\}/i';
            // check if this situation really occurs (otherwise we can end up with infinite nesting)
            if ( preg_match($regexp, $pattern) )
            {
                // extract the globstar from inside the braces and add a new pattern to patterns list
                $patterns[] = preg_replace_callback('/(.+)?\{(.+)?([\*]{2}[^,]?)(.?)\}(.?)/i', function ($matches)
                {

                    $brace = '{' . $matches[ 2 ] . $matches[ 4 ] . '}';
                    if ( $brace === '{,}' || $brace === '{}' )
                    {
                        $brace = '';
                    }
                    $pattern = $matches[ 1 ] . $brace . $matches[ 5 ];

                    return str_replace('//', '/', $pattern);
                }, $pattern);
                // and now change the braces in the main pattern to globstar
                $pattern = preg_replace_callback($regexp, function ($matches)
                {

                    return $matches[ 2 ];
                }, $pattern);
            }
        }
        $files       = [ ];
        $pos         = stripos($pattern, '**');
        $rootPattern = substr($pattern, 0, $pos) . '*';
        $restPattern = substr($pattern, $pos + 2);
        while ( $dirs = glob($rootPattern, GLOB_ONLYDIR | GLOB_BRACE) )
        {
            $rootPattern = $rootPattern . '/*';
            foreach ( $dirs as $dir )
            {
                $patterns[] = $dir . $restPattern;
            }
        }
        foreach ( $patterns as $pat )
        {
            $files = array_merge($files, static::glob($pat, $flags));
        }
        $files = array_unique($files);
        // sort by root first?
        if ( $flags & static::GLOB_ROOTFIRST )
        {
            $files = Arr::sortPaths($files, true);
        }
        else
        {
            if ( $flags & static::GLOB_CHILDFIRST )
            {
                $files = Arr::sortPaths($files, false);
            }
            else
            {
                // default sort order is alphabetically
                sort($files);
            }
        }

        return $files;
    }

    public function isPhar($path)
    {
        return Path::isPhar($path);
    }

    /**
     * Magic call method.
     *
     * @param  string $method
     * @param  mixed  $parameters
     */
    public function __call($method, $parameters)
    {
        if ( method_exists($this->illuminate, $method) || parent::hasMacro($method) )
        {
            return call_user_func_array([ $this->illuminate, $method ], $parameters);
        }
        elseif ( method_exists($this->symfony, $method) )
        {
            return call_user_func_array([ $this->symfony, $method ], $parameters);
        }
        throw new BadMethodCallException("Method [{$method}] not found");
    }


}
