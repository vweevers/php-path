<?php

namespace Weevers\Path;

// Adapted from iojs's path.js@8de78e (March 20th, 2015)
// https://github.com/iojs/io.js/blob/8de78e470d2e291454e2184d7f206c70d4cb8c97/lib/path.js

class Path {
  const scheme_re = '|^[^/\\\]{2,}://|';

  private static $adapter;
  private static $posix;

  public static function selectAdapter($adapter = null) {
    if (self::$posix === null) {
      // Posix adapter is always used for remote streams
      self::$posix = new Adapter\Posix;
    }

    if ($adapter !== null) {
      // Mainly for unit tests
      self::$adapter = is_object($adapter) ? $adapter : new $adapter;
    } else {
      $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
      self::$adapter = $isWindows ? new Adapter\Windows : self::$posix;
    }

    return self::$adapter;
  }

  // resolve([from ...], to)          (nodejs signature)
  // resolve(array([from, ...], to))  (adapter signature)
  public static function resolve() {
    $paths = func_get_args();
    if (count($paths)===1 && is_array($paths[0])) $paths = $paths[0];

    $prefix = [];
    $imploded = null;
    $stripped = [];

    // Strip all stream wrapper schemes, and prepend the
    // last found scheme to the resolved path.
    foreach($paths as $path) {
      $newPrefix = [];
      $path = self::stripScheme($path, $newPrefix);

      if (count($newPrefix)) {
        if ($imploded && $imploded!==implode('', $newPrefix)) {
          // Switched scheme, so skip previous paths (like a root change)
          $stripped = [];
        }

        $prefix = $newPrefix;
        $imploded = implode('', $prefix);
      }

      if ($path) $stripped[] = $path;
    }

    $nonDefault = array_filter($prefix, function($prefix){
      return $prefix!=='file://';
    });

    if (count($nonDefault)===0) $prefix = [];

    // Do a posix-style join for remote URLs and VFS streams
    $nonAbsolutable = array_filter($prefix, function($prefix){
      return $prefix==='vfs://' || !stream_is_local($prefix.'/foo');
    });

    if (count($nonAbsolutable)>0) {
      $method = 'join';
      $adapter = self::$posix;
    } else {
      $method = 'resolve';
      $adapter = self::$adapter;
    }

    $resolved = $adapter->$method($stripped);

    return implode('', $prefix) . $resolved;
  }

  private static function stripScheme($path, &$acc = []) {
    if (preg_match(self::scheme_re, $path, $matches)) {
      $prefix = strtolower($matches[0]);

      // Skip repeated schemes ("file://file://")
      if (!($l = count($acc)) || $prefix!==$acc[$l-1]) $acc[] = $prefix;

      // Recurse in case of combined wrappers ("compress.zlib://php://temp")
      return self::stripScheme(substr($path, strlen($prefix)), $acc);
    }

    return $path;
  }

  public static function getScheme($path) {
    throw new \Exception('getScheme($path) is deprecated because a path can be prefixed '.
      'with more than one scheme. Use getPrefix($path) instead.');
  }

  public static function getPrefix($path, $def = 'file://') {
    $prefixes = [];
    self::stripScheme($path, $prefixes);
    return count($prefixes)>0 ? implode('', $prefixes) : $def;
  }

  public static function isAbsolute($path) {
    return self::$adapter->isAbsolute(self::stripScheme($path));
  }

  public static function relative($from, $to) {
    $from = self::stripScheme($from);
    $to = self::stripScheme($to);
    return self::$adapter->relative($from, $to);
  }

  public static function separator() {
    return self::$adapter->separator();
  }

  public static function delimiter() {
    return self::$adapter->delimiter();
  }

  public static function isCaseSensitive() {
    return self::$adapter->isCaseSensitive();
  }

  // Adapted from github.com/sindresorhus/is-path-inside
  // and github.com/domenic/path-is-inside (TODO: credits)
  public static function isInside($path, $potentialParent) {
    $path = self::resolve($path);
    $potentialParent = self::resolve($potentialParent);

    if ($path===$potentialParent) return false;

    $sep = self::separator();

    // For inside-directory checking, we want to allow trailing slashes, so normalize.
    $path = self::stripTrailingSep($path, $sep);
    $potentialParent = self::stripTrailingSep($potentialParent, $sep);

    if (!self::isCaseSensitive()) {
      $path = strtolower($path);
      $potentialParent = strtolower($potentialParent);
    }

    $len = strlen($potentialParent);

    return strrpos($path, $potentialParent, 0) === 0 &&
      (!isset($path[$len]) || $path[$len] === $sep);
  }

  private static function stripTrailingSep($path, $sep) {
    if (substr($path, -1) === $sep) return substr($path, 0, -1);
    return $path;
  }

  // join([a ...], b)          (nodejs signature)
  // join(array([a, ...], b))  (adapter signature)
  public static function join() {
    $paths = func_get_args();
    if (count($paths)===1 && is_array($paths[0])) $paths = $paths[0];
    return self::$adapter->join($paths);
  }

  public static function normalize($path) {
    return self::$adapter->normalize($path);
  }

  public static function dirname($path) {
    throw new \Exception('Not implemented');
    return self::$adapter->dirname($path);
  }

  public static function basename($path, $ext) {
    throw new \Exception('Not implemented');
    return self::$adapter->basename($path, $ext);
  }

  public static function extname($path) {
    throw new \Exception('Not implemented');
    return self::$adapter->extname($path);
  }

  public static function format(array $pathObject) {
    throw new \Exception('Not implemented');
    return self::$adapter->format($path);
  }

  public static function parse($pathString) {
    throw new \Exception('Not implemented');
    return self::$adapter->parse($pathString);
  }
}

Path::selectAdapter();
