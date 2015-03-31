var common = require('../common');
var assert = require('assert');

var path = require('path');

var isWindows = process.platform === 'win32';

var f = __filename;

assert.equal(path.basename(f), 'test-path.js');
assert.equal(path.basename(f, '.js'), 'test-path');
assert.equal(path.basename(''), '');
assert.equal(path.basename('/dir/basename.ext'), 'basename.ext');
assert.equal(path.basename('/basename.ext'), 'basename.ext');
assert.equal(path.basename('basename.ext'), 'basename.ext');
assert.equal(path.basename('basename.ext/'), 'basename.ext');
assert.equal(path.basename('basename.ext//'), 'basename.ext');

// On Windows a backslash acts as a path separator.
assert.equal(path.win32.basename('\\dir\\basename.ext'), 'basename.ext');
assert.equal(path.win32.basename('\\basename.ext'), 'basename.ext');
assert.equal(path.win32.basename('basename.ext'), 'basename.ext');
assert.equal(path.win32.basename('basename.ext\\'), 'basename.ext');
assert.equal(path.win32.basename('basename.ext\\\\'), 'basename.ext');

// On unix a backslash is just treated as any other character.
assert.equal(path.posix.basename('\\dir\\basename.ext'), '\\dir\\basename.ext');
assert.equal(path.posix.basename('\\basename.ext'), '\\basename.ext');
assert.equal(path.posix.basename('basename.ext'), 'basename.ext');
assert.equal(path.posix.basename('basename.ext\\'), 'basename.ext\\');
assert.equal(path.posix.basename('basename.ext\\\\'), 'basename.ext\\\\');

// POSIX filenames may include control characters
// c.f. http://www.dwheeler.com/essays/fixing-unix-linux-filenames.html
if (!isWindows) {
  var controlCharFilename = 'Icon' + String.fromCharCode(13);
  assert.equal(path.basename('/a/b/' + controlCharFilename),
               controlCharFilename);
}

assert.equal(path.extname(f), '.js');

assert.equal(path.dirname(f).substr(-13),
             isWindows ? 'test\\parallel' : 'test/parallel');
assert.equal(path.dirname('/a/b/'), '/a');
assert.equal(path.dirname('/a/b'), '/a');
assert.equal(path.dirname('/a'), '/');
assert.equal(path.dirname(''), '.');
assert.equal(path.dirname('/'), '/');
assert.equal(path.dirname('////'), '/');

assert.equal(path.win32.dirname('c:\\'), 'c:\\');
assert.equal(path.win32.dirname('c:\\foo'), 'c:\\');
assert.equal(path.win32.dirname('c:\\foo\\'), 'c:\\');
assert.equal(path.win32.dirname('c:\\foo\\bar'), 'c:\\foo');
assert.equal(path.win32.dirname('c:\\foo\\bar\\'), 'c:\\foo');
assert.equal(path.win32.dirname('c:\\foo\\bar\\baz'), 'c:\\foo\\bar');
assert.equal(path.win32.dirname('\\'), '\\');
assert.equal(path.win32.dirname('\\foo'), '\\');
assert.equal(path.win32.dirname('\\foo\\'), '\\');
assert.equal(path.win32.dirname('\\foo\\bar'), '\\foo');
assert.equal(path.win32.dirname('\\foo\\bar\\'), '\\foo');
assert.equal(path.win32.dirname('\\foo\\bar\\baz'), '\\foo\\bar');
assert.equal(path.win32.dirname('c:'), 'c:');
assert.equal(path.win32.dirname('c:foo'), 'c:');
assert.equal(path.win32.dirname('c:foo\\'), 'c:');
assert.equal(path.win32.dirname('c:foo\\bar'), 'c:foo');
assert.equal(path.win32.dirname('c:foo\\bar\\'), 'c:foo');
assert.equal(path.win32.dirname('c:foo\\bar\\baz'), 'c:foo\\bar');
assert.equal(path.win32.dirname('\\\\unc\\share'), '\\\\unc\\share');
assert.equal(path.win32.dirname('\\\\unc\\share\\foo'), '\\\\unc\\share\\');
assert.equal(path.win32.dirname('\\\\unc\\share\\foo\\'), '\\\\unc\\share\\');
assert.equal(path.win32.dirname('\\\\unc\\share\\foo\\bar'),
             '\\\\unc\\share\\foo');
assert.equal(path.win32.dirname('\\\\unc\\share\\foo\\bar\\'),
             '\\\\unc\\share\\foo');
assert.equal(path.win32.dirname('\\\\unc\\share\\foo\\bar\\baz'),
             '\\\\unc\\share\\foo\\bar');


assert.equal(path.extname(''), '');
assert.equal(path.extname('/path/to/file'), '');
assert.equal(path.extname('/path/to/file.ext'), '.ext');
assert.equal(path.extname('/path.to/file.ext'), '.ext');
assert.equal(path.extname('/path.to/file'), '');
assert.equal(path.extname('/path.to/.file'), '');
assert.equal(path.extname('/path.to/.file.ext'), '.ext');
assert.equal(path.extname('/path/to/f.ext'), '.ext');
assert.equal(path.extname('/path/to/..ext'), '.ext');
assert.equal(path.extname('file'), '');
assert.equal(path.extname('file.ext'), '.ext');
assert.equal(path.extname('.file'), '');
assert.equal(path.extname('.file.ext'), '.ext');
assert.equal(path.extname('/file'), '');
assert.equal(path.extname('/file.ext'), '.ext');
assert.equal(path.extname('/.file'), '');
assert.equal(path.extname('/.file.ext'), '.ext');
assert.equal(path.extname('.path/file.ext'), '.ext');
assert.equal(path.extname('file.ext.ext'), '.ext');
assert.equal(path.extname('file.'), '.');
assert.equal(path.extname('.'), '');
assert.equal(path.extname('./'), '');
assert.equal(path.extname('.file.ext'), '.ext');
assert.equal(path.extname('.file'), '');
assert.equal(path.extname('.file.'), '.');
assert.equal(path.extname('.file..'), '.');
assert.equal(path.extname('..'), '');
assert.equal(path.extname('../'), '');
assert.equal(path.extname('..file.ext'), '.ext');
assert.equal(path.extname('..file'), '.file');
assert.equal(path.extname('..file.'), '.');
assert.equal(path.extname('..file..'), '.');
assert.equal(path.extname('...'), '.');
assert.equal(path.extname('...ext'), '.ext');
assert.equal(path.extname('....'), '.');
assert.equal(path.extname('file.ext/'), '.ext');
assert.equal(path.extname('file.ext//'), '.ext');
assert.equal(path.extname('file/'), '');
assert.equal(path.extname('file//'), '');
assert.equal(path.extname('file./'), '.');
assert.equal(path.extname('file.//'), '.');

// On windows, backspace is a path separator.
assert.equal(path.win32.extname('.\\'), '');
assert.equal(path.win32.extname('..\\'), '');
assert.equal(path.win32.extname('file.ext\\'), '.ext');
assert.equal(path.win32.extname('file.ext\\\\'), '.ext');
assert.equal(path.win32.extname('file\\'), '');
assert.equal(path.win32.extname('file\\\\'), '');
assert.equal(path.win32.extname('file.\\'), '.');
assert.equal(path.win32.extname('file.\\\\'), '.');

// On unix, backspace is a valid name component like any other character.
assert.equal(path.posix.extname('.\\'), '');
assert.equal(path.posix.extname('..\\'), '.\\');
assert.equal(path.posix.extname('file.ext\\'), '.ext\\');
assert.equal(path.posix.extname('file.ext\\\\'), '.ext\\\\');
assert.equal(path.posix.extname('file\\'), '');
assert.equal(path.posix.extname('file\\\\'), '');
assert.equal(path.posix.extname('file.\\'), '.\\');
assert.equal(path.posix.extname('file.\\\\'), '.\\\\');

// path.join tests
var failures = [];
var joinTests =
    // arguments                     result
    [[['.', 'x/b', '..', '/b/c.js'], 'x/b/c.js'],
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
if (isWindows) {
  joinTests = joinTests.concat(
    [// UNC path expected
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
    ]);
}

// Run the join tests.
joinTests.forEach(function(test) {
  var actual = path.join.apply(path, test[0]);
  var expected = isWindows ? test[1].replace(/\//g, '\\') : test[1];
  var message = 'path.join(' + test[0].map(JSON.stringify).join(',') + ')' +
                '\n  expect=' + JSON.stringify(expected) +
                '\n  actual=' + JSON.stringify(actual);
  if (actual !== expected) failures.push('\n' + message);
  // assert.equal(actual, expected, message);
});
assert.equal(failures.length, 0, failures.join(''));

// Test thrown TypeErrors
var typeErrorTests = [true, false, 7, null, {}, undefined, [], NaN];

function fail(fn) {
  var args = Array.prototype.slice.call(arguments, 1);

  assert.throws(function() {
    fn.apply(null, args);
  }, TypeError);
}

typeErrorTests.forEach(function(test) {
  fail(path.join, test);
  fail(path.resolve, test);
  fail(path.normalize, test);
  fail(path.isAbsolute, test);
  fail(path.relative, test, 'foo');
  fail(path.relative, 'foo', test);
  fail(path.parse, test);

  // These methods should throw a TypeError, but do not for backwards
  // compatibility. Uncommenting these lines in the future should be a goal.
  // fail(path.dirname, test);
  // fail(path.basename, test);
  // fail(path.extname, test);

  // undefined is a valid value as the second argument to basename
  if (test !== undefined) {
    fail(path.basename, 'foo', test);
  }
});


// path normalize tests
assert.equal(path.win32.normalize('./fixtures///b/../b/c.js'),
             'fixtures\\b\\c.js');
assert.equal(path.win32.normalize('/foo/../../../bar'), '\\bar');
assert.equal(path.win32.normalize('a//b//../b'), 'a\\b');
assert.equal(path.win32.normalize('a//b//./c'), 'a\\b\\c');
assert.equal(path.win32.normalize('a//b//.'), 'a\\b');
assert.equal(path.win32.normalize('//server/share/dir/file.ext'),
             '\\\\server\\share\\dir\\file.ext');

assert.equal(path.posix.normalize('./fixtures///b/../b/c.js'),
             'fixtures/b/c.js');
assert.equal(path.posix.normalize('/foo/../../../bar'), '/bar');
assert.equal(path.posix.normalize('a//b//../b'), 'a/b');
assert.equal(path.posix.normalize('a//b//./c'), 'a/b/c');
assert.equal(path.posix.normalize('a//b//.'), 'a/b');









