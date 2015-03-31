<?php

namespace Weevers\Path;

// Adapted from iojs's path.js@8de78e (March 20th, 2015)
// https://github.com/iojs/io.js/blob/8de78e470d2e291454e2184d7f206c70d4cb8c97/lib/path.js

class Path {
  private static $adapter;

  public static function selectAdapter($adapter = null) {
    if ($adapter !== null) {
      // mainly for unit tests
      self::$adapter = is_object($adapter) ? $adapter : new $adapter;
    } else {
      $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
      self::$adapter = $isWindows ? new Adapter\Windows : new Adapter\Posix;
    }
  }

  // resolve([from ...], to)          (nodejs signature)
  // resolve(array([from, ...], to))  (adapter signature)
  public static function resolve() {
    $paths = func_get_args();
    if (count($paths)===1 && is_array($paths[0])) $paths = $paths[0];

    $outPrefix = '';

    // Support URL wrappers. Strip all schemes, and prepend the 
    // last found scheme to the resolved path.
    $paths = array_map(function($path) use (&$outPrefix){
      $prefix = '';
      $path = self::stripScheme($path, $prefix);
      if ($prefix) $outPrefix = $prefix;
      return $path;
    }, $paths);

    return $outPrefix . self::$adapter->resolve($paths);
  }

  private static function stripScheme($path, &$prefix = null, $expectedPrefix = null) {
    if ($pos = strpos($path, '://')) { // not false and more than 0
      $prefix = substr($path, 0, $pos+3);
      $stripped = substr($path, $pos+3);
    } else {
      $prefix = '';
      $stripped = $path;
    }

    if ($expectedPrefix!==null && !self::equalPrefixes($prefix, $expectedPrefix)) {
      throw new \UnexpectedValueException(sprintf(
        'Schemes do not match: expected "%s" for "%s"', $expectedPrefix, $path
      ));
    }

    return $stripped;
  }

  public static function getScheme($path, $def = 'file') {
    $pos = strpos($path, '://');
    if (!$pos) return $def;
    return substr($path, 0, $pos);
  }

  private static function equalPrefixes($a, $b) {
    // Default scheme is file
    if ($a==='') $a = 'file://';
    if ($b==='') $b = 'file://';

    return $a === $b;
  }

  public static function isAbsolute($path) {
    return self::$adapter->isAbsolute(self::stripScheme($path));
  }

  public static function relative($from, $to) {
    $fromPrefix = ''; $toPrefix = '';

    $strippedFrom = self::stripScheme($from, $fromPrefix);
    $strippedTo = self::stripScheme($to, $toPrefix, $fromPrefix);

    return self::$adapter->relative($strippedFrom, $strippedTo);
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
    $prefix1 = ''; $prefix2 = '';

    $path = self::resolve(self::stripScheme($path, $prefix1));
    $potentialParent = self::resolve(self::stripScheme($potentialParent, $prefix2, $prefix1));

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

    throw new \Exception('Not implemented');
    return $this->adapter->join($paths);
  }

  public static function normalize($path) {
    throw new \Exception('Not implemented');
    return $this->adapter->normalize($path);
  }

  public static function dirname($path) {
    throw new \Exception('Not implemented');
    return $this->adapter->dirname($path);
  }

  public static function basename($path, $ext) {
    throw new \Exception('Not implemented');
    return $this->adapter->basename($path, $ext);
  }

  public static function extname($path) {
    throw new \Exception('Not implemented');
    return $this->adapter->extname($path);
  }

  public static function format(array $pathObject) {
    throw new \Exception('Not implemented');
    return $this->adapter->format($path);
  }

  public static function parse($pathString) {
    throw new \Exception('Not implemented');
    return $this->adapter->parse($pathString);
  }
}

Path::selectAdapter();
