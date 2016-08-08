<?php

namespace Laradic\Filesystem;

use Laradic\Support\ServiceProvider;

/**
* The main service provider
*
* @author        Laradic
* @copyright  Copyright (c) 2015, Laradic
* @license      http://mit-license.org MIT
*/
class FilesystemServiceProvider extends ServiceProvider
{
    #protected $configFiles = [ 'laradic.filesystem' ];

    protected $shared = [
        'fs' => Filesystem::class
    ];


}
