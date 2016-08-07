<?php
/**
 * Part of the Laradic PHP packages.
 *
 * License and copyright information bundled with this package in the LICENSE file
 */


namespace Laradic\Filesystem;


class Temp
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var String
     */
    protected $prefix;

    /**
     * @var \SplFileInfo[]
     */
    protected $files = array();

    /**
     * @var Bool
     */
    protected $preserveRunFolder = false;

    /**
     *
     * If temp folder needs to be deterministic, you can use ID as the last part of folder name
     *
     * @var string
     */
    protected $id = "";

    public function __construct($prefix = '', Filesystem $fs = null)
    {
        $this->prefix = $prefix;
        $this->filesystem = $fs === null ? Filesystem::create() : $fs;
        $this->id = uniqid("run-", true);
    }

    public function initRunFolder()
    {
        clearstatcache();
        if ( !file_exists($this->getTmpPath()) && !is_dir($this->getTmpPath()) ) {
            $this->filesystem->mkdir($this->getTmpPath());
        }
    }

    /**
     * @param bool $value
     */
    public function setPreserveRunFolder($value)
    {
        $this->preserveRunFolder = $value;
    }

    /**
     * Get path to temp directory
     *
     * @return string
     */
    protected function getTmpPath()
    {
        $tmpDir = sys_get_temp_dir();
        if ( !empty($this->prefix) ) {
            $tmpDir .= "/" . $this->prefix;
        }
        $tmpDir .= "/" . $this->id;
        return $tmpDir;
    }

    /**
     * Returns path to temp folder for current request
     *
     * @return string
     */
    public function getTmpFolder()
    {
        return $this->getTmpPath();
    }

    /**
     * Create empty file in TMP directory
     *
     * @param string $suffix filename suffix
     * @param bool $preserve
     * @throws \Exception
     * @return \SplFileInfo
     */
    public function createTmpFile($suffix = null, $preserve = false)
    {
        $this->initRunFolder();
        $file = uniqid();
        if ( $suffix ) {
            $file .= '-' . $suffix;
        }
        $fileInfo = new \SplFileInfo($this->getTmpPath() . '/' . $file);
        $this->filesystem->touch($fileInfo);
        $this->files[] = array(
            'file' => $fileInfo,
            'preserve' => $preserve
        );
        $this->filesystem->chmod($fileInfo, 0600);
        return $fileInfo;
    }

    /**
     * Creates named temporary file
     *
     * @param $fileName
     * @param bool $preserve
     * @return \SplFileInfo
     * @throws \Exception
     */
    public function createFile($fileName, $preserve = false)
    {
        $this->initRunFolder();
        $fileInfo = new \SplFileInfo($this->getTmpPath() . '/' . $fileName);
        $this->filesystem->touch($fileInfo);
        $this->files[] = array(
            'file' => $fileInfo,
            'preserve' => $preserve
        );
        $this->filesystem->chmod($fileInfo, 0600);
        return $fileInfo;
    }

    /**
     *
     * Set temp id
     *
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Destructor
     *
     * Delete all files created by syrup component run
     */
    function __destruct()
    {
        $preserveRunFolder = $this->preserveRunFolder;
        $fs = Filesystem::create();
        foreach ( $this->files as $file ) {
            if ( $file[ 'preserve' ] ) {
                $preserveRunFolder = true;
            }
            if ( file_exists($file[ 'file' ]) && is_file($file[ 'file' ]) && !$file[ 'preserve' ] ) {
                $fs->remove($file[ 'file' ]);
            }
        }
        if ( !$preserveRunFolder ) {
            $fs->remove($this->getTmpPath());
        }
    }
}
