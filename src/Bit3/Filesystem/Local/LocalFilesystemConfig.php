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
 * A filesystem object
 *
 * @package php-filesystem
 * @author  Oliver Hoff <oliver@hofff.com>
 */
class LocalFilesystemConfig
	extends AbstractFilesystemConfig
{
	public function __construct() {
		parent::__construct();
	}
}