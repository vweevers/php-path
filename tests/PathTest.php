<?php

namespace Weevers\Path;

// TODO: swap expected/actual arguments for assertEquals (differs 
// from node's `assert.equal`)
class PathTest extends AbstractTest {
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
    $test = function($resolveTests, $adapter = null) {
      $adapter = Path::selectAdapter($adapter);

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
         [['/some/dir', '.', '/absolute/'], '/absolute']];

    $test($posix, $this->posix);

    // For these tests, the result depends on the OS and adapter
    $cwdtests = [
      [['.'], getcwd()],
      [['a/b/c/', '../../..'], getcwd()]
    ];

    $test($cwdtests);
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

  public function testJoin() {
    $joinTests = [
      // arguments                    result
      [['.', 'x/b', '..', '/b/c.js'], 'x/b/c.js'],
      [['/.', 'x/b', '..', '/b/c.js'], '/x/b/c.js'],
      [['/foo', '../../../bar'], '/bar'],
      [['foo', '../../../bar'], '../../bar'],
      [['foo/', '../../../bar'], '../../bar'],
      [['foo/x', '../../../bar'], '../bar'],
      [['foo/x', './bar'], 'foo/x/bar'],
      [['foo/x/', './bar'], 'foo/x/bar'],
      [['foo/x/', '.', 'bar'], 'foo/x/bar'],
      [['./'], './'],
      [['.', './'], './'],
      [['.', '.', '.'], '.'],
      [['.', './', '.'], '.'],
      [['.', '/./', '.'], '.'],
      [['.', '/////./', '.'], '.'],
      [['.'], '.'],
      [['', '.'], '.'],
      [['', 'foo'], 'foo'],
      [['foo', '/bar'], 'foo/bar'],
      [['', '/foo'], '/foo'],
      [['', '', '/foo'], '/foo'],
      [['', '', 'foo'], 'foo'],
      [['foo', ''], 'foo'],
      [['foo/', ''], 'foo/'],
      [['foo', '', '/bar'], 'foo/bar'],
      [['./', '..', '/foo'], '../foo'],
      [['./', '..', '..', '/foo'], '../../foo'],
      [['.', '..', '..', '/foo'], '../../foo'],
      [['', '..', '..', '/foo'], '../../foo'],
      [['/'], '/'],
      [['/', '.'], '/'],
      [['/', '..'], '/'],
      [['/', '..', '..'], '/'],
      [[''], '.'],
      [['', ''], '.'],
      [[' /foo'], ' /foo'],
      [[' ', 'foo'], ' /foo'],
      [[' ', '.'], ' '],
      [[' ', '/'], ' /'],
      [[' ', ''], ' '],
      [['/', 'foo'], '/foo'],
      [['/', '/foo'], '/foo'],
      [['/', '//foo'], '/foo'],
      [['/', '', '/foo'], '/foo'],
      [['', '/', 'foo'], '/foo'],
      [['', '/', '/foo'], '/foo']
    ];

    // Windows-specific join tests
    $windowsTests = [
      // UNC path expected
      [['//foo/bar'], '//foo/bar/'],
      [['\\/foo/bar'], '//foo/bar/'],
      [['\\\\foo/bar'], '//foo/bar/'],
      // UNC path expected - server and share separate
      [['//foo', 'bar'], '//foo/bar/'],
      [['//foo/', 'bar'], '//foo/bar/'],
      [['//foo', '/bar'], '//foo/bar/'],
      // UNC path expected - questionable
      [['//foo', '', 'bar'], '//foo/bar/'],
      [['//foo/', '', 'bar'], '//foo/bar/'],
      [['//foo/', '', '/bar'], '//foo/bar/'],
      // UNC path expected - even more questionable
      [['', '//foo', 'bar'], '//foo/bar/'],
      [['', '//foo/', 'bar'], '//foo/bar/'],
      [['', '//foo/', '/bar'], '//foo/bar/'],
      // No UNC path expected (no double slash in first component)
      [['\\', 'foo/bar'], '/foo/bar'],
      [['\\', '/foo/bar'], '/foo/bar'],
      [['', '/', '/foo/bar'], '/foo/bar'],
      // No UNC path expected (no non-slashes in first component - questionable)
      [['//', 'foo/bar'], '/foo/bar'],
      [['//', '/foo/bar'], '/foo/bar'],
      [['\\\\', '/', '/foo/bar'], '/foo/bar'],
      [['//'], '/'],
      // No UNC path expected (share name missing - questionable).
      [['//foo'], '/foo'],
      [['//foo/'], '/foo/'],
      [['//foo', '/'], '/foo/'],
      [['//foo', '', '/'], '/foo/'],
      // No UNC path expected (too many leading slashes - questionable)
      [['///foo/bar'], '/foo/bar'],
      [['////foo', 'bar'], '/foo/bar'],
      [['\\\\\\/foo/bar'], '/foo/bar'],
      // Drive-relative vs drive-absolute paths. This merely describes the
      // status quo, rather than being obviously right
      [['c:'], 'c:.'],
      [['c:.'], 'c:.'],
      [['c:', ''], 'c:.'],
      [['', 'c:'], 'c:.'],
      [['c:.', '/'], 'c:./'],
      [['c:.', 'file'], 'c:file'],
      [['c:', '/'], 'c:/'],
      [['c:', 'file'], 'c:/file']
    ];

    foreach($joinTests as $test) {
      $adapter = $this->posix;
      $message = "{$adapter}->join(" . json_encode($test[0]) . ')';                    
      $this->assertEquals($test[1], $adapter->join($test[0]), $message);

      $adapter = $this->windows;
      $message = "{$adapter}->join(" . json_encode($test[0]) . ')';
      $expected = str_replace('/', '\\', $test[1]);
      $this->assertEquals($expected, $adapter->join($test[0]), $message);
    }

    foreach($windowsTests as $test) {
      $adapter = $this->windows;
      $message = "{$adapter}->join(" . json_encode($test[0]) . ')';
      $expected = str_replace('/', '\\', $test[1]);
      $this->assertEquals($expected, $adapter->join($test[0]), $message);
    }
  }

  public function testNormalize() {
    $this->assertEquals($this->windows->normalize('./fixtures///b/../b/c.js'),
                 'fixtures\\b\\c.js');
    $this->assertEquals($this->windows->normalize('/foo/../../../bar'), '\\bar');
    $this->assertEquals($this->windows->normalize('a//b//../b'), 'a\\b');
    $this->assertEquals($this->windows->normalize('a//b//./c'), 'a\\b\\c');
    $this->assertEquals($this->windows->normalize('a//b//.'), 'a\\b');
    $this->assertEquals($this->windows->normalize('//server/share/dir/file.ext'),
                 '\\\\server\\share\\dir\\file.ext');

    $this->assertEquals($this->posix->normalize('./fixtures///b/../b/c.js'),
                 'fixtures/b/c.js');
    $this->assertEquals($this->posix->normalize('/foo/../../../bar'), '/bar');
    $this->assertEquals($this->posix->normalize('a//b//../b'), 'a/b');
    $this->assertEquals($this->posix->normalize('a//b//./c'), 'a/b/c');
    $this->assertEquals($this->posix->normalize('a//b//.'), 'a/b');
  }

  public function testGetPrefix() {
    $paths = [
      'file://foo' => 'file://',
      'file:///foo' => 'file://',
      'file://file://foo' => 'file://',
      'glob://file://foo' => 'glob://file://',
      'foo' => 'file://',
      '//foo' => 'file://',
      'invalid:\\foo' => 'file://',
      'compress.zlib://php://temp' => 'compress.zlib://php://'
    ];

    foreach($paths as $path => $expected) {
      $this->assertEquals($expected, Path::getPrefix($path));
    }

    $this->assertEquals('beep://', Path::getPrefix('foo', 'beep://'));
    $this->assertEquals('file://', Path::getPrefix('file://foo', 'beep://'));
  }
}
