<?php

namespace Weevers\Path\Adapter;

abstract class AbstractAdapter {
  abstract public function __toString();

  protected function preg_matches($pattern, $subject) {
    $matches = [];
    preg_match($pattern, $subject, $matches);
    return $matches;
  }

  protected function inspect($var) {
    return json_encode($var);
  }

  protected function assertPath($path) {
    if (!is_string($path)) {
      throw new \InvalidArgumentException(
        'Path must be a string. Received '.$this->inspect($var)
      );
    }
  }

  // resolves . and .. elements in a path array with directory names there
  // must be no slashes or device names (c:\) in the array
  // (so also no leading and trailing slashes - it does not distinguish
  // relative and absolute paths)
  protected function normalizeArray($parts, $allowAboveRoot) {
    $res = [];

    for ($i = 0; $i < count($parts); $i++) {
      $p = $parts[$i];
      
      // ignore empty paths
      if (!$p || $p === '.') continue;

      if ($p === '..') {
        if (count($res) && $res[count($res) - 1] !== '..') {
          array_pop($res);
        } elseif ($allowAboveRoot) {
          $res[] =  '..';
        }
      } else {
        $res[] = $p;
      }
    }

    return $res;
  }

  protected function trimRelativeArray($arr) {
    $start = 0;

    for (; $start < count($arr); $start++) {
      if ($arr[$start] !== '') break;
    }

    $end = count($arr) - 1;
    
    for (; $end >= 0; $end--) {
      if ($arr[$end] !== '') break;
    }

    if ($start > $end) return [];
    return array_slice($arr, $start, $end + 1);
  }
}
