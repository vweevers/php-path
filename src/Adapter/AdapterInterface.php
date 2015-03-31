<?php

namespace Weevers\Path\Adapter;

interface AdapterInterface {
  public function resolve(array $paths);
  public function isAbsolute($path);
  public function relative ($from, $to);
  public function join(array $paths);
  public function normalize($path);

  public function separator();
  public function delimiter();
  public function isCaseSensitive();

  // These methods are not yet implemented
  
  // public function dirname($path)
  // public function basename($path, $ext)
  // public function extname($path)
  
  // public function format(array $pathObject)
  // public function parse($pathString)
}
