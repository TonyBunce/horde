<?php
/**
 * All tests for the Horde_Autoloader_Cache:: package.
 *
 * PHP version 5
 *
 * @category   Horde
 * @package    Autoloader_Cache
 * @subpackage UnitTests
 * @author     Gunnar Wrobel <wrobel@horde.org>
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @link       http://www.horde.org/libraries/Horde_Autoloader_Cache
 */

/**
 * Define the main method
 */
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Horde_Autoloader_Cache_AllTests::main');
}

/**
 * Prepare the test setup.
 */
require_once 'Horde/Test/AllTests.php';

/**
 * All tests for the Horde_Autoloader_Cache:: package.
 *
 * @category   Horde
 * @package    Autoloader_Cache
 * @subpackage UnitTests
 * @author     Gunnar Wrobel <wrobel@horde.org>
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @link       http://www.horde.org/libraries/Horde_Autoloader_Cache
 */
class Horde_Autoloader_Cache_AllTests extends Horde_Test_AllTests
{
}

Horde_Autoloader_Cache_AllTests::init('Horde_Autoloader_Cache', __FILE__);

if (PHPUnit_MAIN_METHOD == 'Horde_Autoloader_Cache_AllTests::main') {
    Horde_Autoloader_Cache_AllTests::main();
}