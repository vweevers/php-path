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
