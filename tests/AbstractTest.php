<?php

namespace Weevers\Path;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase {
  public function setUp() {
    $this->windows = new Adapter\Windows;
    $this->posix = new Adapter\Posix;
    $this->isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
  }
}
