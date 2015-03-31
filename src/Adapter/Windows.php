<?php

namespace Weevers\Path\Adapter;

class Windows extends AbstractAdapter implements AdapterInterface {
  // Regex to split a windows path into three parts: [*, device, slash,
  // tail] windows-only
  const splitDeviceRe = ';^([a-zA-Z]:|[\\\/]{2}[^\\\/]+[\\\/]+[^\\\/]+)?([\\\/])?([\s\S]*?)$;';

  // Regex to split the tail part of the above into [*, dir, basename, ext]
  const splitTailRe = ';^([\s\S]*?)((?:\.{1,2}|[^\\\/]+?|)(\.[^.\/\\]*|))(?:[\\\/]*)$;';

  const sep = '\\';
  const delimiter = ';';

  public function separator() {
    return self::sep;
  }

  public function delimiter() {
    return self::delimiter;
  }

  public function isCaseSensitive() {
    return false;
  }

  // Function to split a filename into [root, dir, basename, ext]
  protected function win32SplitPath($filename) {
    // Separate device+slash from tail
    $result = $this->preg_matches(static::splitDeviceRe, $filename);
    $device = (isset($result[1]) ? $result[1] : '') . (isset($result[2]) ? $result[2] : '');
    $tail = (isset($result[3]) ? $result[3] : '');

    // Split the tail into dir, basename and extension
    $result2 = $this->preg_matches($splitTailRe, $tail);
    $dir = $result2[1];
    $basename = $result2[2];
    $ext = $result2[3];

    return [$device, $dir, $basename, $ext];
  }

  protected function normalizeUNCRoot($device) {
    return '\\\\' . preg_replace('|[\\\/]+|', '\\', // removed modifier: g
      preg_replace('|^[\\\/]+|', '', $device));
  }

  public function resolve(array $paths) {
    $resolvedDevice = '';
    $resolvedTail = '';
    $resolvedAbsolute = false;

    for ($i = count($paths) - 1; $i >= -1; $i--) {   
      if ($i >= 0) {
        $path = $paths[$i];
      } else if (!$resolvedDevice) {
        $path = getcwd();
      } else {
        // Windows has the concept of drive-specific current working
        // directories. If we've resolved a drive letter but not yet an
        // absolute path, get cwd for that drive. We're sure the device is not
        // an unc path at this points, because unc paths are always absolute.
        $path = $_ENV['=' . $resolvedDevice];
        // Verify that a drive-local cwd was found and that it actually points
        // to our drive. If not, default to the drive's root.
        if (!$path || strtolower(substr($path, 0, 3)) !==
            strtolower($resolvedDevice) . '\\') {
          $path = $resolvedDevice . '\\';
        }
      }

      $this->assertPath($path);

      // Skip empty entries
      if ($path === '') {
        continue;
      }

      $result = $this->preg_matches(static::splitDeviceRe, $path);
      $device = isset($result[1]) ? $result[1] : '';
      $isUnc = $device && $device[1] !== ':';
      $isAbsolute = $this->isAbsolute($path);
      $tail = $result[3];

      if ($device &&
          $resolvedDevice &&
          strtolower($device) !== strtolower($resolvedDevice)) {
        // This path points to another device so it is not applicable
        continue;
      }

      if (!$resolvedDevice) {
        $resolvedDevice = $device;
      }
      if (!$resolvedAbsolute) {
        $resolvedTail = $tail . '\\' . $resolvedTail;
        $resolvedAbsolute = $isAbsolute;
      }

      if ($resolvedDevice && $resolvedAbsolute) {
        break;
      }
    }

    // Convert slashes to backslashes when `resolvedDevice` points to an UNC
    // root. Also squash multiple slashes into a single one where appropriate.
    if ($isUnc) {
      $resolvedDevice = $this->normalizeUNCRoot($resolvedDevice);
    }

    // At this point the path should be resolved to a full absolute path,
    // but handle relative paths to be safe (might happen when process.cwd()
    // fails)

    // Normalize the tail path
    $resolvedTail = implode('\\', $this->normalizeArray(
      preg_split('|[\\\/]+|', $resolvedTail),
      !$resolvedAbsolute)
    );

    return ($resolvedDevice . ($resolvedAbsolute ? '\\' : '') . $resolvedTail) ?:
           '.';
  }

  public function isAbsolute($path){
    $this->assertPath($path);

    $result = $this->preg_matches(static::splitDeviceRe, $path);
    $device = isset($result[1]) ? $result[1] : '';
    $isUnc = !!$device && $device[1] !== ':';

    // UNC paths are always absolute
    return !!$result[2] || $isUnc;
  }

  // path.relative(from, to)
  // it will solve the relative path from 'from' to 'to', for instance:
  // from = 'C:\\orandea\\test\\aaa'
  // to = 'C:\\orandea\\impl\\bbb'
  // The output of the function should be: '..\\..\\impl\\bbb'
  public function relative ($from, $to) {
    $this->assertPath($from);
    $this->assertPath($to);

    $from = $this->resolve([$from]);
    $to = $this->resolve([$to]);

    // windows is not case sensitive
    $lowerFrom = strtolower($from);
    $lowerTo = strtolower($to);

    $toParts = $this->trimRelativeArray(explode('\\', $to));
    $lowerFromParts = $this->trimRelativeArray(explode('\\', $lowerFrom));
    $lowerToParts = $this->trimRelativeArray(explode('\\', $lowerTo));

    $length = min(count($lowerFromParts), count($lowerToParts));
    $samePartsLength = $length;
    for ($i = 0; $i < $length; $i++) {
      if ($lowerFromParts[$i] !== $lowerToParts[$i]) {
        $samePartsLength = $i;
        break;
      }
    }

    if ($samePartsLength == 0) {
      return $to;
    }

    $outputParts = [];
    for ($i = $samePartsLength; $i < count($lowerFromParts); $i++) {
      $outputParts[] = '..';
    }

    $outputParts = array_merge($outputParts, array_slice($toParts, $samePartsLength));

    return implode('\\', $outputParts);
  }

  public function __toString() {
    return 'windows';
  }

  // Commented out code below is half-converted to PHP

  // WinPath->normalize = function(path) {
  //   static::assertPath($path);

  //   $result = preg_match(static::splitDeviceRe, $path),
  //       $device = $result[1] || '',
  //       $isUnc = $device && $device[1] !== ':',
  //       $isAbsolute = static::isAbsolute($path),
  //       $tail = $result[3],
  //       $trailingSlash = /[\\\/]$/.test($tail);

  //   // Normalize the tail path
  //   $tail = static::normalizeArray($tail.split(/[\\\/]+/), !$isAbsolute).join('\\');

  //   if (!$tail && !$isAbsolute) {
  //     $tail = '.';
  //   }
  //   if ($tail && $trailingSlash) {
  //     $tail += '\\';
  //   }

  //   // Convert slashes to backslashes when `device` points to an UNC root.
  //   // Also squash multiple slashes into a single one where appropriate.
  //   if ($isUnc) {
  //     $device = static::normalizeUNCRoot($device);
  //   }

  //   return $device . ($isAbsolute ? '\\' : '') . $tail;
  // };

  // WinPath->join = function() {
  //   function f(p) {
  //     if (typeof p !== 'string') {
  //       throw new TypeError('Arguments to path.join must be strings');
  //     }
  //     return p;
  //   }

  //   $paths = Array.prototype.filter.call(arguments, f);
  //   $joined = $paths.join('\\');

  //   // Make sure that the joined path doesn't start with two slashes, because
  //   // normalize() will mistake it for an UNC path then.
  //   //
  //   // This step is skipped when it is very clear that the user actually
  //   // intended to point at an UNC path. This is assumed when the first
  //   // non-empty string arguments starts with exactly two slashes followed by
  //   // at least one more non-slash character.
  //   //
  //   // Note that for normalize() to treat a path as an UNC path it needs to
  //   // have at least 2 components, so we don't filter for that here.
  //   // This means that the user can use join to construct UNC paths from
  //   // a server name and a share name; for example:
  //   //   path.join('//server', 'share') -> '\\\\server\\share\')
  //   if (!/^[\\\/]{2}[^\\\/]/.test($paths[0])) {
  //     $joined = $joined.replace(/^[\\\/]{2,}/, '\\');
  //   }

  //   return WinPath->normalize($joined);
  // };

  // WinPath->_makeLong = function(path) {
  //   // Note: this will *probably* throw somewhere.
  //   if (typeof $path !== 'string')
  //     return $path;

  //   if (!$path) {
  //     return '';
  //   }

  //   $resolvedPath = WinPath->resolve($path);

  //   if (/^[a-zA-Z]\:\\/.test($resolvedPath)) {
  //     // path is local filesystem path, which needs to be converted
  //     // to long UNC path.
  //     return '\\\\?\\' + $resolvedPath;
  //   } else if (/^\\\\[^?.]/.test($resolvedPath)) {
  //     // path is network UNC path, which needs to be converted
  //     // to long UNC path.
  //     return '\\\\?\\UNC\\' + $resolvedPath.substring(2);
  //   }

  //   return $path;
  // };

  // WinPath->dirname = function(path) {
  //   $result = static::win32SplitPath($path),
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

  // WinPath->basename = function(path, ext) {
  //   if ($ext !== undefined && typeof $ext !== 'string')
  //     throw new TypeError('ext must be a string');

  //   $f = static::win32SplitPath($path)[2];
  //   // TODO: make this comparison case-insensitive on windows?
  //   if ($ext && $f.substr(-1 * count($ext)) === $ext) {
  //     $f = $f.substr(0, count($f) - count($ext));
  //   }
  //   return $f;
  // };


  // WinPath->extname = function(path) {
  //   return static::win32SplitPath($path)[3];
  // };


  // WinPath->format = function(pathObject) {
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

  //   $dir = $pathObject.dir;
  //   $base = $pathObject.base || '';
  //   if ($dir.slice(count($dir) - 1, count($dir)) === WinPath->sep) {
  //     return $dir + $base;
  //   }

  //   if ($dir) {
  //     return $dir + WinPath->sep + $base;
  //   }

  //   return $base;
  // };


  // WinPath->parse = function(pathString) {
  //   static::assertPath($pathString);

  //   $allParts = static::win32SplitPath($pathString);
  //   if (!$allParts || count($allParts) !== 4) {
  //     throw new TypeError("Invalid path '" + $pathString + "'");
  //   }
  //   return {
  //     root: $allParts[0],
  //     dir: $allParts[0] + $allParts[1].slice(0, $allParts[1].length - 1),
  //     base: $allParts[2],
  //     ext: $allParts[3],
  //     name: $allParts[2].slice(0, $allParts[2].length - $allParts[3].length)
  //   };
  // };
}
