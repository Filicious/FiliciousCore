<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 * @link    http://filicious.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Filicious\Test;

use Filicious\Exception\FileNotFoundException;
use Filicious\Exception\NotAFileException;
use Filicious\Stream;
use Filicious\Stream\StreamMode;
use PHPUnit_Framework_TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-10-17 at 10:24:36.
 */
abstract class AbstractSingleFilesystemStreamTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var SingleFilesystemTestEnvironment
	 */
	protected $environment;

	/**
	 * @var TestAdapter
	 */
	protected $adapter;

	/**
	 * @var \Filicious\Filesystem
	 */
	protected $fs;

	protected $files = array(
		'example.txt',
		'zap/file.txt',
	);

	protected $contents = array(
		'example.txt'  => 'The world is like a pizza!',
		'zap/file.txt' => 'Hello World! Everything is fine :)',
	);

	protected $dirs = array(
		'foo',
		'foo/bar',
		'zap',
	);

	protected $links = array(
		'foo/file.lnk' => 'file',
		'zap/bar.lnk'  => 'dir',
	);

	protected $notExists = array(
		'does_not_exists.missing',
		'foo/does_not_exists.missing',
		'foo/bar/does_not_exists.missing',
		'zap/does_not_exists.missing',
	);

	/**
	 * @return SingleFilesystemTestEnvironment
	 */
	abstract protected function setUpEnvironment();

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->environment = $this->setUpEnvironment();
		$this->adapter     = $this->environment->getAdapter();
		$this->fs          = $this->environment->getFilesystem();

		// create directory <path>/foo/bar/
		$this->adapter->createDirectory('/foo');
		$this->adapter->createDirectory('/foo/bar');

		// create directory <path>/zap
		$this->adapter->createDirectory('/zap');

		// create file <path>/example.txt
		$this->adapter->putContents('/example.txt', $this->contents['example.txt']);

		// create file <path>/zap/file.txt
		$this->adapter->putContents('/zap/file.txt', $this->contents['zap/file.txt']);

		// enable streaming on the filesystem
		$host = substr(md5(uniqid()), 0, 8);
		$this->fs->enableStreaming($host, 'test');
	}

	protected function tearDown()
	{
		$this->fs->disableStreaming();
		$this->environment->cleanup();
	}

	/**
	 * @covers Filicious\File::getStream()
	 */
	public function testGetStream()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file   = $this->fs->getFile($pathname);
			$stream = $file->getStream();
			$this->assertTrue($stream instanceof Stream);
		}

		// test directories
		foreach ($this->dirs as $pathname) {
			$file = $this->fs->getFile($pathname);
			$stream = $file->getStream();
			$this->assertTrue($stream instanceof Stream);
		}

		// test non existing files
		foreach ($this->notExists as $pathname) {
			$file = $this->fs->getFile($pathname);
			try {
				$file->getStream();
				$this->fail('Open stream on non existing file does NOT throw a FileNotFoundException.');
			}
			catch (FileNotFoundException $e) {
				// hide
			}
			catch (\Exception $e) {
				$this->fail(
					'Open stream on non existing file does NOT throw a FileNotFoundException, got a ' . get_class($e)
				);
			}
		}
	}

	/**
	 * @covers Filicious\Stream::open()
	 * @covers Filicious\Stream::close()
	 */
	public function testStreamOpenClose()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file     = $this->fs->getFile($pathname);
			$url      = $file->getStreamURL();
			$resource = fopen($url, 'r');

			$this->assertTrue(is_resource($resource));

			if (is_resource($resource)) {
				$this->assertTrue(fclose($resource));
			}
			else {
				$this->markTestIncomplete('Open stream failed, could not test close!');
			}
		}
	}

	/**
	 * @covers Filicious\Stream::cast()
	 */
	public function testCast()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file     = $this->fs->getFile($pathname);
			$url      = $file->getStreamURL();
			$resource = fopen($url, 'r');

			$read   = array($resource);
			$write  = null;
			$except = null;

			$this->assertTrue(false !== stream_select($read, $write, $except, 0));
		}
	}

	/**
	 * @covers Filicious\Stream::stat()
	 */
	public function testStat()
	{
		$this->markTestIncomplete('stat currently not supported by the api.'); // TODO
		return;

		$self = $this;

		$test = function ($stat, $pathname) use ($self) {
			$self->assertTrue(isset($stat['dev']));
			$self->assertTrue(isset($stat['ino']));
			$self->assertTrue(isset($stat['mode']));
			$self->assertTrue(isset($stat['nlink']));
			$self->assertTrue(isset($stat['uid']));
			$self->assertTrue(isset($stat['gid']));
			$self->assertTrue(isset($stat['rdev']));
			$self->assertTrue(isset($stat['size']));
			$self->assertTrue(isset($stat['atime']));
			$self->assertTrue(isset($stat['mtime']));
			$self->assertTrue(isset($stat['ctime']));
			$self->assertTrue(isset($stat['blksize']));
			$self->assertTrue(isset($stat['blocks']));

			$this->assertEquals($this->adapter->stat($pathname), $stat);
		};

		// test files
		foreach ($this->files as $pathname) {
			$file = $this->fs->getFile($pathname);
			$url  = $file->getStreamURL();

			// from stream object
			$stream = $file->getStream();
			$stream->open(new StreamMode('r'));
			$stat = $stream->stat();
			$stream->close();
			$test($stat, $pathname);

			// from opened stream
			$resource = fopen($url, 'r');
			$stat     = fstat($resource);
			fclose($resource);
			$test($stat, $pathname);

			// from url
			$stat = stat($url);
			$test($stat, $pathname);
		}
	}

	/**
	 * @covers Filicious\Stream::lock()
	 */
	public function testLock()
	{
		$this->markTestIncomplete();
	}

	/**
	 * @covers Filicious\Stream::read()
	 * @covers Filicious\Stream::write()
	 * @covers Filicious\Stream::seek()
	 * @covers Filicious\Stream::tell()
	 * @covers Filicious\Stream::eof()
	 * @covers Filicious\Stream::truncate()
	 */
	public function testStreaming()
	{
		// test files
		foreach ($this->files as $pathname) {
			$file     = $this->fs->getFile($pathname);
			$url      = $file->getStreamURL();
			$content  = $this->contents[$pathname];
			$length   = strlen($content);
			$n        = (int) floor($length / 5);
			$resource = fopen($url, 'rb+');


			// test tell on start
			$this->assertEquals(
				0,
				ftell($resource)
			);
			// test eof on start
			$this->assertFalse(
				feof($resource)
			);


			// test read from start
			$this->assertEquals(
				substr($content, 0, $n),
				fread($resource, $n)
			);
			// test tell after read
			$this->assertEquals(
				$n,
				ftell($resource)
			);
			// test eof after read
			$this->assertFalse(
				feof($resource)
			);


			// test seek set
			$this->assertEquals(
				0,
				fseek($resource, 2 * $n, SEEK_SET)
			);
			// test tell after seek set
			$this->assertEquals(
				2 * $n,
				ftell($resource)
			);
			// test eof after seek set
			$this->assertFalse(
				feof($resource)
			);
			// test read after seek set
			$this->assertEquals(
				substr($content, 2 * $n, $n),
				fread($resource, $n)
			);


			// test seek add
			$this->assertEquals(
				0,
				fseek($resource, $n, SEEK_CUR)
			);
			// test tell after seek add
			$this->assertEquals(
				4 * $n,
				ftell($resource)
			);
			// test eof after seek add
			$this->assertFalse(
				feof($resource)
			);
			// test read after seek add
			$this->assertEquals(
				substr($content, 4 * $n),
				fread($resource, $length)
			);
			// test eof after read
			$this->assertTrue(
				feof($resource)
			);


			// test seek append
			$this->assertEquals(
				0,
				fseek($resource, 5, SEEK_END)
			);
			// test tell after seek append
			$this->assertEquals(
				$length + 5,
				ftell($resource)
			);
			// test eof after seek append
			$this->assertFalse(
				feof($resource)
			);
			// test read after seek append
			$this->assertEquals(
				'',
				fread($resource, strlen($content))
			);


			// test write
			$this->assertEquals(
				$length,
				fwrite($resource, $content)
			);
			fflush($resource);
			$newContent = $content . "\0\0\0\0\0" . $content;
			fseek($resource, 0);
			$this->assertEquals(
				$newContent,
				fread($resource, 3 * $length)
			);

			// test truncate
			if (version_compare(phpversion(), '5.4', '<')) {
				$this->markTestSkipped(
					'ftruncate() on custom wrappers not supported in PHP 5.3, see https://bugs.php.net/bug.php?id=53888'
				);
			}
			else {
				$this->assertEquals(
					2 * $length + 5,
					$this->adapter->getFileSize($pathname)
				);
				ftruncate($resource, $length);
				fflush($resource);
				fclose($resource);
				$this->assertEquals(
					$length,
					$this->adapter->getFileSize($pathname)
				);
			}
		}
	}

	/**
	 * @covers Filicious\Stream\StreamWrapper::mkdir()
	 * @covers Filicious\Stream\StreamWrapper::rename()
	 * @covers Filicious\Stream\StreamWrapper::rmdir()
	 * @covers Filicious\Stream\StreamWrapper::unlink()
	 */
	public function testPhpFileOperations()
	{
		$root    = $this->fs->getRoot();
		$rootURL = $root->getStreamURL();

		$directory1 = 'test1';
		$directory2 = 'test2';

		$file1 = $directory2 . '/file1';
		$file2 = $directory2 . '/file2';

		// test create directory
		$this->assertTrue(
			mkdir($rootURL . $directory1)
		);
		$this->assertTrue(
			$this->adapter->isDirectory($directory1)
		);
		$this->assertFalse(
			$this->adapter->isDirectory($directory2)
		);

		// test rename directory
		$this->assertTrue(
			rename(
				$rootURL . $directory1,
				$rootURL . $directory2
			)
		);
		$this->assertFalse(
			$this->adapter->isDirectory($directory1)
		);
		$this->assertTrue(
			$this->adapter->isDirectory($directory2)
		);

		// test touch file
		if (version_compare(phpversion(), '5.4', '<')) {
			$this->markTestSkipped(
				'touch() on custom wrappers not supported in PHP 5.3, see http://php.net/manual/en/streamwrapper.stream-metadata.php'
			);
		}
		else {
			$this->assertTrue(
				touch($rootURL . $file1)
			);
			$this->assertTrue(
				$this->adapter->exists($file1)
			);
			$this->assertTrue(
				$this->adapter->isFile($file1)
			);
		}

		// test chmod file
		if (version_compare(phpversion(), '5.4', '<')) {
			$this->markTestSkipped(
				'chmod() on custom wrappers not supported in PHP 5.3, see http://php.net/manual/en/streamwrapper.stream-metadata.php'
			);
		}
		else {
			$this->assertTrue(
				chmod($rootURL . $file1, 0700)
			);
			$this->assertEquals(
				'0700',
				substr(sprintf('%o', $this->adapter->getMode($file1)), -4)
			);
		}

		// test chown file
		// TODO

		// test chmod file
		// TODO

		// test rename file
		$this->assertTrue(
			rename(
				$rootURL . $file1,
				$rootURL . $file2
			)
		);
		$this->assertFalse(
			$this->adapter->exists($file1)
		);
		$this->assertTrue(
			$this->adapter->exists($file2)
		);
		$this->assertTrue(
			$this->adapter->isFile($file2)
		);

		// test unlink file
		$this->assertTrue(
			unlink($rootURL . $file2)
		);
		$this->assertFalse(
			$this->adapter->exists($file2)
		);

		// test rmdir directory
		$this->assertTrue(
			rmdir($rootURL . $directory2)
		);
		$this->assertFalse(
			$this->adapter->exists($directory2)
		);
	}

	/**
	 * @covers Filicious\Stream\StreamWrapper::dir_opendir()
	 * @covers Filicious\Stream\StreamWrapper::dir_closedir()
	 * @covers Filicious\Stream\StreamWrapper::dir_readdir()
	 * @covers Filicious\Stream\StreamWrapper::dir_rewinddir()
	 */
	public function testPhpDirOperation()
	{
		$root    = $this->fs->getRoot();
		$rootURL = $root->getStreamURL();

		$this->assertEquals(
			array_values(
				array_filter(
					$this->adapter->scandir('/'),
					function ($entry) {
						return $entry != '.' && $entry != '..';
					}
				)
			),
			scandir($rootURL)
		);
	}
}
