# path

PHP 5.4 port of the node/iojs [path](https://iojs.org/api/path.html) module, because PHP is like a family holiday dinner and I want to survive. Description from the iojs documentation:

> This module contains utilities for handling and transforming file paths. Almost all these methods perform only string transformations. The file system is not consulted to check whether paths are valid.

[![packagist status](https://img.shields.io/packagist/v/weevers/path.svg?style=flat-square)](https://packagist.org/packages/weevers/path) [![Travis build status](https://img.shields.io/travis/vweevers/php-path.svg?style=flat-square&label=travis)](http://travis-ci.org/vweevers/php-path) [![AppVeyor build status](https://img.shields.io/appveyor/ci/vweevers/php-path.svg?style=flat-square&label=appveyor)](https://ci.appveyor.com/project/vweevers/php-path) [![Dependency status](https://www.versioneye.com/user/projects/551a81123661f1bee500007b/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/551a81123661f1bee500007b)

Jump to: [usage](#usage) / [install](#install) / [license](#license)

## examples

Example output on a posix system, assuming "/please" is the current working directory:

```php
<?php
use Weevers\Path\Path;

// "../../tolerate/php"
Path::relative('i/love/node', 'i/tolerate/php');

// "/alright" (resolve works like a sequence of cd commands in a shell)
Path::resolve('ah', '/okay', '../alright');

// true
Path::isInside('parent/child', 'parent');

// "/please/start/dancing"
Path::resolve('start', 'dancing');

?>
```

## usage

Usage docs pending. In the meantime, consult the [iojs documentation](https://iojs.org/api/path.html), because the function signatures are almost the same and the code (including unit tests) copied.

### Ported so far (for posix and windows), including unit tests

- `relative($from, $to)`
- `isAbsolute($path)`
- `resolve($path, ..)`
- `join($path, ..)`
- `normalize($path)`
- `separator()`
- `delimiter()`

### PHP-specific additions

`resolve()` also accepts stream wrapper URI's. This is implemented outside of the (ported) posix and Windows adapters, to ensure it doesn't interfere with the original behavior (which remains well tested and has a frozen stability).

- `file` schemes are stripped, unless combined with other schemes 
- A scheme change is interpreted like a root change. E.g. going from `http://x` to `glob://x` acts like a `cd` from `c:\x` to `d:\x`.
- For remote streams (like `http`) and [vfs](https://github.com/mikey179/vfsStream) streams, the arguments passed to `resolve()` are joined (instead of resolved to an absolute path) with forward slashes (regardless of OS).

Some examples (for Windows, so you can see the difference between local and remote streams), assuming "C:\project" is the current working directory:

```php
<?php

// Relative input: "c:\project\beep"
Path::resolve('file://beep');
Path::resolve('beep');

// Absolute input: "c:\beep"
Path::resolve('file:///beep');
Path::resolve('/beep');

// Combined wrappers: "zip://file://c:\beep"
Path::resolve('zip://file:///beep');

// Remote stream, so forward slashes: "zip://http://example.com/blog/2015"
Path::resolve('zip://http://example.com\blog', '2015');

// "zip://c:\project\bar"
Path::resolve('zip://dir', '..', 'bar');
Path::resolve('dir', 'zip://..', 'bar');

// A VFS URI stays relative: "vfs://root/virtual.file"
Path::resolve('vfs://root', 'virtual.file');

// A scheme change: "glob://c:\project\*.js"
Path::resolve('http://example.com', 'glob://*.js');

// No scheme change: "glob://c:\project\dir\*.js"
Path::resolve('glob://dir', 'glob://*.js');

// UNC (a network path): "compress.zlib://\\server\share\resource"
// Note that "\\server\share" is the root, so ".." has no effect
Path::resolve('compress.zlib:////server/share', '..', 'resource');

?>
```

### Additional methods

- `isInside($path, $parent)` - similar to [sindresorhus/is-path-inside](https://github.com/sindresorhus/is-path-inside) for node
- `getPrefix($path)` - returns "zip://" for "zip://2015.zip#april/02.log"

## install

With [composer](https://getcomposer.org/) do:

```
composer require weevers/path
```

## license

[MIT](http://opensource.org/licenses/MIT) © [Vincent Weevers](http://vincentweevers.nl) and [iojs/node authors](https://github.com/iojs/io.js/blob/v1.x/AUTHORS).
