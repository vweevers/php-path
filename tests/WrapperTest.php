<?php

namespace Weevers\Path;

class WrapperTest extends AbstractTest {
  public function testResolve() {
    $test = function($resolveTests, $adapter = null) {
      $adapter = Path::selectAdapter($adapter);

      foreach($resolveTests as $test) {
        $actual = Path::resolve($test[0]);
        $expected = $test[1];

        if (isset($test[2]) && $test[2]) { // unixify
          $expected = str_replace('\\', '/', $expected);
          $actual = str_replace('\\', '/', $actual);
        }

        $this->assertEquals($expected, $actual, 'Path::resolve(' . json_encode($test[0]) . ')');
      }
    };

    // Note: the order of combined wrappers doesn't make sense here,
    // but that doesn't matter. It's more important that the schemes
    // we use are registered (i.e., built-in).
    $windows = [
      // should remove file:// schemes
      [['c:/blah\\blah', 'file://d:/games', 'c:../a'], 'c:\\blah\\a'],
      [['c:/ignore', 'file://FILE://d:\\a/b\\c/d', '\\e.exe'], 'd:\\e.exe'],
      
      // unless combined with another scheme
      [['c:/ignore', 'glob://file://c:/some/file'], 'glob://file://c:\\some\\file'],
      
      // should not remove other schemes
      [['d:/ignore', 'glob://d:some/dir//'], 'glob://d:\\ignore\\some\\dir'],
      [['glob://d:/ignore', 'd:some/dir//'], 'glob://d:\\ignore\\some\\dir'],

      // should join vfs and remote streams, posix-style
      [['vfs://foo', '/bar'], 'vfs://foo/bar'],
      [['http://example.com', '//dir'], 'http://example.com/dir'],

      // a new scheme should be interpreted as a root change
      [['glob://bar', 'http://foo'], 'http://foo'],
      [['glob://c:\bar', 'file://c:\bar'], 'c:\\bar'],
      [['glob://c:\bar', 'file://c:\bar', 'bop'], 'c:\\bar\\bop'],
      [['glob://c:\bar', 'c:\bar', 'file://c:\bop'], 'c:\\bop'],
      [['c:\one', 'c:\two', 'glob://c:\three'], 'glob://c:\\three'],

      // unless its the same scheme
      [['file://c:\bar', 'file://foo'], 'c:\\bar\\foo'],
      [['file://c:\bar', 'file://foo', 'bee'], 'c:\\bar\\foo\\bee'],
      [['file://c:\bar', 'file://file://foo'], 'c:\\bar\\foo'],
      [['file://glob://c:\bar', 'file://glob://foo'], 'file://glob://c:\\bar\\foo'],

      // UNC paths
      [['glob:////server/share', '..', 'relative'], 'glob://\\\\server\\share\\relative'],
      [['glob://c:/', '//server/share'], 'glob://\\\\server\\share\\'],
      [['file://glob://c:/', '//server//share'], 'file://glob://\\\\server\\share\\'],
      [['glob://file://file://glob://c:/', '///some//dir'], 'glob://file://glob://c:\\some\\dir']
    ];

    $test($windows, $this->windows);

    $posix = [
      [['/var/lib', 'glob://../', 'file/'], 'glob:///var/file'],
      [['/var/lib', 'file:///../', 'file/'], '/file'],
      [['/some/dir', '.', '/file:/absolute/'], '/file:/absolute'],

      // Empty paths
      [['glob://', '/foo'], 'glob:///foo'],
      [['file://', '/foo', 'bar'], '/foo/bar'],
      [['glob://'], 'glob://'.getcwd(), true]
    ];

    $test($posix, $this->posix);
  }
}
