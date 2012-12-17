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

namespace Filicious\Test\Local;

require_once(__DIR__ . '/../../../bootstrap.php');

use Filicious\Local\LocalFilesystem;
use Filicious\Local\LocalFilesystemConfig;
use Filicious\Test\AbstractSingleFilesystemTest;


/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-10-17 at 10:24:36.
 */
class LocalFilesystemTest extends AbstractSingleFilesystemTest
{
	protected $temporaryPath;

	/**
	 * Set up the adapter and filesystem.
	 */
	protected function setUpEnvironment()
	{
		/** create a test structure */
		$this->temporaryPath = tempnam(sys_get_temp_dir(), 'php_filesystem_test_');
		unlink($this->temporaryPath);
		mkdir($this->temporaryPath);

		$this->adapter = new LocalTestAdapter($this->temporaryPath);

		$this->fs = new LocalFilesystem(new LocalFilesystemConfig($this->temporaryPath));
	}

	/**
	 * Clean up adapter and filesystem.
	 */
	protected function cleanUpEnvironment()
	{
		// delete temporary files
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(
				$this->temporaryPath,
				\FilesystemIterator::SKIP_DOTS));

		/** @var \SplFileInfo $path */
		foreach ($iterator as $path) {
			$this->realDeleteFile($path->getPathname());
		}

		// delete temporary directories
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(
				$this->temporaryPath),
			\RecursiveIteratorIterator::CHILD_FIRST);

		/** @var \SplFileInfo $path */
		foreach ($iterator as $path) {
			if ($path->getBasename() != '.' and $path->getBasename() != '..') {
				$this->realDeleteDirectory($path->getPathname());
			}
		}
	}
}
