<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem;


/**
 * Skeleton for FilesystemConfig implementors.
 *
 * @package php-filesystem
 * @author  Oliver Hoff <oliver@hofff.com>
 */
abstract class AbstractFilesystemConfig
	implements FilesystemConfig
{

	/* (non-PHPdoc)
	 * @see Bit3\Filesystem.FilesystemConfig::create()
	 */
	public static function create() {
		$args = func_get_args();
		$clazz = new ReflectionClass(get_called_class());
		return $clazz->newInstanceArgs($args);
	}
	
	protected function __construct() {
	}
	
	protected $immutable;
	
	/* (non-PHPdoc)
	 * @see Bit3\Filesystem.FilesystemConfig::immutable()
	 */
	public function immutable() {
		$this->immutable = true;
	}
	
	public function __clone() {
		unset($this->immutable);
	}
	
	protected function checkImmutable() {
		if($this->immutable) {
			throw new Exception('Config is immutable'); // TODO
		}
	}
	
	protected $basePath;
	
	/* (non-PHPdoc)
	 * @see Bit3\Filesystem.FilesystemConfig::getBasePath()
	 */
	public function getBasePath() {
		return $this->basePath;
	}
	
	/* (non-PHPdoc)
	 * @see Bit3\Filesystem.FilesystemConfig::setBasePath()
	 */
	public function setBasePath($basePath) {
		$this->checkImmutable();
		$this->basePath = (string) $basePath;
	}
	
	/* (non-PHPdoc)
	 * @see Bit3\Filesystem.FilesystemConfig::normalizeBasePath()
	 */
	public function normalizeBasePath() {
		$this->basePath = Util::normalizePath('/' . $this->basePath) . '/';
	}
	
	protected $provider;
	
	/* (non-PHPdoc)
	 * @see Bit3\Filesystem.FilesystemConfig::getPublicURLProvider()
	 */
	public function getPublicURLProvider() {
		return $this->provider;
	}
	
	/* (non-PHPdoc)
	 * @see Bit3\Filesystem.FilesystemConfig::setPublicURLProvider()
	 */
	public function setPublicURLProvider(PublicURLProvider $provider = null) {
		$this->checkImmutable();
		$this->provider = $provider;
	}
}