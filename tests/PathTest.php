<?php

namespace Weevers\Path;

class PathTest extends \PHPUnit_Framework_TestCase {
  public function setUp() {
    $this->windows = new Adapter\Windows;
    $this->posix = new Adapter\Posix;
  }

  public function testIsInside() {
    Path::selectAdapter($this->posix);
    
    $this->assertTrue(Path::isInside('/path/foo', '/path'));
    $this->assertTrue(Path::isInside('/path/foo/', '/path'));
    $this->assertTrue(Path::isInside('/path/foo', '/path/'));
    $this->assertTrue(Path::isInside('/path/foo/', '/path/'));
    $this->assertTrue(Path::isInside('/path/foo/bar', '/path'));
    $this->assertTrue(Path::isInside('/path/foo/../bar', '/path'));
    $this->assertTrue(Path::isInside('beep/boop', 'beep'));

    $this->assertFalse(Path::isInside('/path', '/path'));
    $this->assertFalse(Path::isInside('/path/..', '/'));
    $this->assertFalse(Path::isInside('/path', '/path/foo'));
    $this->assertFalse(Path::isInside('/bop', '/bap'));
  }

  public function testResolve() {
    $test = function($resolveTests, $adapter) {
      Path::selectAdapter($adapter);
      foreach($resolveTests as $test) {
        $actual = Path::resolve($test[0]);
        $expected = $test[1];
        $this->assertEquals($expected, $actual, 'Path::resolve(' . json_encode($test[0]) . ')');

        $actual = $adapter->resolve($test[0]);
        $this->assertEquals($expected, $actual, "{$adapter}->resolve(" . json_encode($test[0]) . ')');
      }
    };

    $windows =
        // arguments                                    result
        [[['c:/blah\\blah', 'd:/games', 'c:../a'], 'c:\\blah\\a'],
         [['c:/ignore', 'd:\\a/b\\c/d', '\\e.exe'], 'd:\\e.exe'],
         [['c:/ignore', 'c:/some/file'], 'c:\\some\\file'],
         [['d:/ignore', 'd:some/dir//'], 'd:\\ignore\\some\\dir'],
         [['.'], getcwd()],
         [['//server/share', '..', 'relative\\'], '\\\\server\\share\\relative'],
         [['c:/', '//'], 'c:\\'],
         [['c:/', '//dir'], 'c:\\dir'],
         [['c:/', '//server/share'], '\\\\server\\share\\'],
         [['c:/', '//server//share'], '\\\\server\\share\\'],
         [['c:/', '///some//dir'], 'c:\\some\\dir']
    ];

    $test($windows, $this->windows);

    $posix =
        // arguments                                    result
        [[['/var/lib', '../', 'file/'], '/var/file'],
         [['/var/lib', '/../', 'file/'], '/file'],
         [['a/b/c/', '../../..'], getcwd()],
         [['.'], getcwd()],
         [['/some/dir', '.', '/absolute/'], '/absolute']];

    $test($posix, $this->posix);
  }

  public function testIsAbsolute() {
    $this->assertTrue($this->windows->isAbsolute('//server/file'));
    $this->assertTrue($this->windows->isAbsolute('\\\\server\\file'));
    $this->assertTrue($this->windows->isAbsolute('C:/Users/'));
    $this->assertTrue($this->windows->isAbsolute('C:\\Users\\'));

    $this->assertFalse($this->windows->isAbsolute('C:cwd/another'));
    $this->assertFalse($this->windows->isAbsolute('C:cwd\\another'));
    $this->assertFalse($this->windows->isAbsolute('directory/directory'));
    $this->assertFalse($this->windows->isAbsolute('directory\\directory'));

    $this->assertTrue($this->posix->isAbsolute('/home/foo'));
    $this->assertTrue($this->posix->isAbsolute('/home/foo/..'));

    $this->assertFalse($this->posix->isAbsolute('bar/'));
    $this->assertFalse($this->posix->isAbsolute('./baz'));
  }

  public function testSepAndDelim() {
    $this->assertEquals($this->windows->separator(), '\\');
    $this->assertEquals($this->windows->delimiter(), ';');

    $this->assertEquals($this->posix->separator(), '/');
    $this->assertEquals($this->posix->delimiter(), ':');

    Path::selectAdapter($this->windows);
    $this->assertEquals(Path::separator(), '\\');
    $this->assertEquals(Path::delimiter(), ';');

    Path::selectAdapter($this->posix);
    $this->assertEquals(Path::separator(), '/');
    $this->assertEquals(Path::delimiter(), ':');
  }

  public function testRelative() {
    $test = function($relativeTests, $adapter) {
      Path::selectAdapter($adapter);
      foreach($relativeTests as $test) {
        $actual = Path::relative($test[0], $test[1]);
        $expected = $test[2];
        $args = array_slice($test, 0, 2);
        $this->assertEquals($expected, $actual, 'Path::relative(' . json_encode($args) . ')');

        $actual = $adapter->relative($test[0], $test[1]);
        $this->assertEquals($expected, $actual, "{$adapter}->relative(" . json_encode($args) . ')');
      }
    };

    $windows =
        // arguments                     result
        [['c:/blah\\blah', 'd:/games', 'd:\\games'],
         ['c:/aaaa/bbbb', 'c:/aaaa', '..'],
         ['c:/aaaa/bbbb', 'c:/cccc', '..\\..\\cccc'],
         ['c:/aaaa/bbbb', 'c:/aaaa/bbbb', ''],
         ['c:/aaaa/bbbb', 'c:/aaaa/cccc', '..\\cccc'],
         ['c:/aaaa/', 'c:/aaaa/cccc', 'cccc'],
         ['c:/', 'c:\\aaaa\\bbbb', 'aaaa\\bbbb'],
         ['c:/aaaa/bbbb', 'd:\\', 'd:\\']];

    $test($windows, $this->windows);

    $posix =
        // arguments                    result
        [['/var/lib', '/var', '..'],
         ['/var/lib', '/bin', '../../bin'],
         ['/var/lib', '/var/lib', ''],
         ['/var/lib', '/var/apache', '../apache'],
         ['/var/', '/var/lib', 'lib'],
         ['/', '/var/lib', 'var/lib']];

    $test($posix, $this->posix);
  }

  public function testReadme() {
    Path::selectAdapter($this->posix);

    $this->assertEquals('../../tolerate/php', 
      Path::relative('i/love/node', 'i/tolerate/php'));
    $this->assertEquals('/cwd/ugh/sigh/alright', 
      Path::resolve('/cwd', 'ugh/beep', '../sigh', 'alright')); 
  }
}
