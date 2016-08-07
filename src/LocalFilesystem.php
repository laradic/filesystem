<?php
/**
 * Part of the Laradic PHP packages.
 *
 * License and copyright information bundled with this package in the LICENSE file
 */


namespace Laradic\Filesystem;

use Illuminate\Contracts\Filesystem\Cloud as CloudFilesystemContract;
use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;
use Symfony\Component\Finder\Finder;

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
class LocalFilesystem implements FilesystemContract, CloudFilesystemContract
{
    protected $fs;

    public function __construct()
    {
        $this->fs = Filesystem::create();
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
     * Get the contents of a file.
     *
     * @param  string $path
     *
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function get($path)
    {
        return $this->fs->get($path);
    }

    /**
     * Write the contents of a file.
     *
     * @param  string          $path
     * @param  string|resource $contents
     * @param  string          $visibility
     *
     * @return bool
     */
    public function put($path, $contents, $visibility = null)
    {
        if ($visibility !== null) {
            $this->setVisibility($path, $visibility);
        }

        return $this->fs->put($path, $contents);
    }

    /**
     * Determine if a file exists.
     *
     * @param  string $path
     *
     * @return bool
     */
    public function exists($path)
    {
        return $this->fs->exists($path);
    }

    /**
     * Get the visibility for the given path.
     *
     * @param  string $path
     *
     * @return string
     */
    public function getVisibility($path)
    {
    }

    /**
     * Set the visibility for the given path.
     *
     * @param  string $path
     * @param  string $visibility
     *
     * @return void
     */
    public function setVisibility($path, $visibility)
    {
    }

    /**
     * Prepend to a file.
     *
     * @param  string $path
     * @param  string $data
     *
     * @return int
     */
    public function prepend($path, $data)
    {
        return $this->fs->prepend($path, $data);
    }

    /**
     * Append to a file.
     *
     * @param  string $path
     * @param  string $data
     *
     * @return int
     */
    public function append($path, $data)
    {
        return $this->fs->append($path, $data);
    }

    /**
     * Delete the file at a given path.
     *
     * @param  string|array $paths
     *
     * @return bool
     */
    public function delete($paths)
    {
        return $this->fs->delete($paths);
    }

    /**
     * Copy a file to a new location.
     *
     * @param  string $from
     * @param  string $to
     *
     * @return bool
     */
    public function copy($from, $to)
    {
        return $this->fs->copy($from, $to);
    }

    /**
     * Move a file to a new location.
     *
     * @param  string $from
     * @param  string $to
     *
     * @return bool
     */
    public function move($from, $to)
    {
        return $this->fs->move($from, $to);
    }

    /**
     * Get the file size of a given file.
     *
     * @param  string $path
     *
     * @return int
     */
    public function size($path)
    {
        return $this->fs->size($path);
    }

    /**
     * Get the file's last modification time.
     *
     * @param  string $path
     *
     * @return int
     */
    public function lastModified($path)
    {
        return $this->fs->lastModified($path);
    }

    /**
     * Get an array of all files in a directory.
     *
     * @param  string|null $directory
     * @param  bool        $recursive
     *
     * @return array
     */
    public function files($directory = null, $recursive = false)
    {
        return $this->fs->files($directory);
    }

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param  string|null $directory
     *
     * @return array
     */
    public function allFiles($directory = null)
    {
        return $this->fs->allFiles($directory);
    }

    /**
     * Get all of the directories within a given directory.
     *
     * @param  string|null $directory
     * @param  bool        $recursive
     *
     * @return array
     */
    public function directories($directory = null, $recursive = false)
    {

        $directories = [ ];

        foreach (Finder::create()->in($directory)->directories() as $dir) {
            $directories[] = $dir->getPathname();
        }

        return $directories;
    }

    /**
     * Get all (recursive) of the directories within a given directory.
     *
     * @param  string|null $directory
     *
     * @return array
     */
    public function allDirectories($directory = null)
    {
        return $this->fs->directories($directory);
    }

    /**
     * Create a directory.
     *
     * @param  string $path
     *
     * @return bool
     */
    public function makeDirectory($path)
    {
        return $this->fs->makeDirectory($path, 0755, true);
    }

    /**
     * Recursively delete a directory.
     *
     * @param  string $directory
     *
     * @return bool
     */
    public function deleteDirectory($directory)
    {
        // TODO: Implement deleteDirectory() method.
    }
}
