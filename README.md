# path

Partial PHP 5.4 port of the node/iojs [path](https://iojs.org/api/path.html) module:

> This module contains utilities for handling and transforming file paths. Almost all these methods perform only string transformations. The file system is not consulted to check whether paths are valid.

PHP is like a family holiday dinner, so to survive the awkwardness and plastered smiles of WordPress pride, I asked my girlfriend `path` to come. **Currently implemented (for posix and windows)**: `relative($from, $to)`, `isAbsolute($path)`, `resolve($path, ..)`, `separator()`, `delimiter()` and an extra `isInside($path, $parent)` method (similar to [sindresorhus/is-path-inside](https://github.com/sindresorhus/is-path-inside) for node).

[![packagist status](https://img.shields.io/packagist/v/weevers/path.svg?style=flat-square)](https://packagist.org/packages/weevers/path) [![Travis build status](https://img.shields.io/travis/vweevers/php-path.svg?style=flat-square&label=travis)](http://travis-ci.org/vweevers/php-path) [![AppVeyor build status](https://img.shields.io/appveyor/ci/vweevers/php-path.svg?style=flat-square&label=appveyor)](https://ci.appveyor.com/project/vweevers/php-path) [![Dependency status](https://www.versioneye.com/user/projects/551a81123661f1bee500007b/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/551a81123661f1bee500007b)

Jump to: [usage](#usage) / [install](#install) / [license](#license)

## example

```php
<?php
use Weevers\Path\Path
  , Earth\Species\Mother;

// "../../tolerate/php"
Path::relative('i/love/node', 'i/tolerate/php');

// "/cwd/ugh/sigh/alright"
Path::resolve('ugh/beep', '../sigh', 'alright'); 

$mom = new Mother();

// "/conflict" (resolve works like a sequence of cd commands in a shell)
$mom->attemptsTo( Path::resolve('/a', '/conflict') );

// very true
$mom->isAwkward();

// true until birth
Path::isInside('/parent/child', '/parent');

// On Windows: "C:\cwd\please\dont\start\dancing"
// On posix systems: "/cwd/please/dont/start/dancing"
Path::resolve('please', 'dont/start', 'dancing');

?>
```

## usage

### `Path::*`

Usage docs pending. In the meantime, consult the [iojs documentation](https://iojs.org/api/path.html), because the function signatures are almost the same and the code (including unit tests) copied.

## install

With [composer](https://getcomposer.org/) do:

```
composer require weevers/path
```

## license

[MIT](http://opensource.org/licenses/MIT) © [Vincent Weevers](http://vincentweevers.nl) and [iojs/node authors](https://github.com/iojs/io.js/blob/v1.x/AUTHORS).
