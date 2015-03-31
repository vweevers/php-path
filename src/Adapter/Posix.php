<?php

namespace Weevers\Path\Adapter;

class Posix extends AbstractAdapter implements AdapterInterface {

  // Split a filename into [root, dir, basename, ext], unix version
  // 'root' is just a slash, or nothing.
  const splitPathRe =
    ';^(\/?|)([\s\S]*?)((?:\.{1,2}|[^\/]+?|)(\.[^.\/]*|))(?:[\/]*)$;';

  const sep = '/';
  const delimiter = ':';

  public function separator() {
    return self::sep;
  }

  public function delimiter() {
    return self::delimiter;
  }

  public function isCaseSensitive() {
    return true;
  }

  public function isAbsolute($path) {
    $this->assertPath($path);
    return isset($path[0]) && $path[0] === '/';
  }

  public function join(array $paths) {
    $path = '';

    foreach($paths as $segment) {
      $this->assertPath($segment);

      if ($segment) {
        if (!$path) $path.= $segment;
        else $path.= '/' . $segment;
      }
    }

    return $this->normalize($path);
  }

  public function normalize($path) {
    $this->assertPath($path);

    $isAbsolute = $this->isAbsolute($path);
    $trailingSlash = substr($path, -1) === '/';

    // Normalize the path
    $path = implode('/', $this->normalizeArray(explode('/', $path), !$isAbsolute));

    if (!$path && !$isAbsolute) $path = '.';
    if ($path && $trailingSlash) $path.= '/';

    return ($isAbsolute ? '/' : '') . $path;
  }

  public function resolve(array $paths) {
    $resolvedPath = '';
    $resolvedAbsolute = false;

    for ($i = count($paths) - 1; $i >= -1 && !$resolvedAbsolute; $i--) {
      $path = ($i >= 0) ? $paths[$i] : getcwd();

      $this->assertPath($path);

      // Skip empty entries
      if ($path === '') {
        continue;
      }

      $resolvedPath = $path . '/' . $resolvedPath;
      $resolvedAbsolute = isset($path[0]) && $path[0] === '/';
    }

    // At this point the path should be resolved to a full absolute path, but
    // handle relative paths to be safe (might happen when process.cwd() fails)

    // Normalize the path
    $resolvedPath = implode('/', $this->normalizeArray(
      explode('/', $resolvedPath), !$resolvedAbsolute));

    return (($resolvedAbsolute ? '/' : '') . $resolvedPath) ?: '.';
  }

  public function relative ($from, $to) {
    $this->assertPath($from);
    $this->assertPath($to);

    $from = substr($this->resolve([$from]), 1);
    $to = substr($this->resolve([$to]), 1);

    $fromParts = $this->trimRelativeArray(explode('/', $from));
    $toParts = $this->trimRelativeArray(explode('/', $to));

    $length = min(count($fromParts), count($toParts));
    $samePartsLength = $length;

    for ($i = 0; $i < $length; $i++) {
      if ($fromParts[$i] !== $toParts[$i]) {
        $samePartsLength = $i;
        break;
      }
    }

    $outputParts = [];
    for ($i = $samePartsLength; $i < count($fromParts); $i++) {
      $outputParts[] = '..';
    }

    $outputParts = array_merge($outputParts, array_slice($toParts, $samePartsLength));

    return implode('/', $outputParts);
  }

  protected function posixSplitPath($filename) {
    return array_slice(preg_split(self::splitPathRe, $filename), 1);
  }

  public function __toString() {
    return 'posix';
  }
}

// Commented out code below is half-converted to PHP

// $this->dirname = function(path) {
//   $result = posixSplitPath($path),
//       $root = $result[0],
//       $dir = $result[1];

//   if (!$root && !$dir) {
//     // No dirname whatsoever
//     return '.';
//   }

//   if ($dir) {
//     // It has a dirname, strip trailing slash
//     $dir = $dir.substr(0, count($dir) - 1);
//   }

//   return $root + $dir;
// };


// $this->basename = function(path, ext) {
//   if ($ext !== undefined && typeof $ext !== 'string')
//     throw new TypeError('ext must be a string');

//   $f = posixSplitPath($path)[2];

//   if ($ext && $f.substr(-1 * count($ext)) === $ext) {
//     $f = $f.substr(0, count($f) - count($ext));
//   }
//   return $f;
// };


// $this->extname = function(path) {
//   return posixSplitPath($path)[3];
// };


// $this->format = function(pathObject) {
//   if ($pathObject === null || typeof $pathObject !== 'object') {
//     throw new TypeError(
//         "Parameter 'pathObject' must be an object, not " + typeof $pathObject
//     );
//   }

//   $root = $pathObject.root || '';

//   if (typeof $root !== 'string') {
//     throw new TypeError(
//         "'pathObject.root' must be a string or undefined, not " +
//         typeof $pathObject.root
//     );
//   }

//   $dir = $pathObject.dir ? $pathObject.dir + $this->sep : '';
//   $base = $pathObject.base || '';
//   return $dir + $base;
// };


// $this->parse = function(pathString) {
//   $this->assertPath($pathString);

//   $allParts = posixSplitPath($pathString);
//   if (!$allParts || count($allParts) !== 4) {
//     throw new TypeError("Invalid path '" + $pathString + "'");
//   }
//   $allParts[1] = $allParts[1] || '';
//   $allParts[2] = $allParts[2] || '';
//   $allParts[3] = $allParts[3] || '';

//   return {
//     root: $allParts[0],
//     dir: $allParts[0] + $allParts[1].slice(0, $allParts[1].length - 1),
//     base: $allParts[2],
//     ext: $allParts[3],
//     name: $allParts[2].slice(0, $allParts[2].length - $allParts[3].length)
//   };
// };



