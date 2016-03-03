<?php
/**
 * JBZoo PimpleDumper
 *
 * This file is part of the JBZoo CCK package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package   PimpleDumper
 * @license   MIT
 * @copyright Copyright (C) JBZoo.com,  All rights reserved.
 * @link      https://github.com/JBZoo/PimpleDumper
 * @author    Denis Smetannikov <denis@jbzoo.com>
 */

namespace JBZoo\PHPUnit;

use JBZoo\PimpleDumper\PimpleDumper;
use Pimple\Container;

/**
 * Class PimpleDumperTest
 * @package JBZoo\PHPUnit
 */
class PimpleDumperTest extends PHPUnit
{
    protected function setUp()
    {
        $file = PROJECT_ROOT . '/pimple.json';
        if (file_exists($file)) {
            unlink($file);
        }

        $file = PROJECT_ROOT . '/.phpstorm.meta.php';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function testSorting()
    {
        $pimple = new Container();
        $dumper = new PimpleDumper();

        $pimple['n_2'] = 2;
        $pimple['n_1'] = 1;
        $pimple['n_3'] = 3;

        isSame([
            ['name' => 'n_1', 'type' => 'int', 'value' => 1],
            ['name' => 'n_2', 'type' => 'int', 'value' => 2],
            ['name' => 'n_3', 'type' => 'int', 'value' => 3],
        ], $this->_toJson($dumper->dumpPimple($pimple)));
    }

    public function testTypes()
    {
        $pimple    = new Container();
        $subPimple = new Container();
        $dumper    = new PimpleDumper();

        $subPimple['t_array'] = [];
        $subPimple['f_array'] = function () {
            return [];
        };

        $pimple['t_array']   = [];
        $pimple['t_pimple']  = $subPimple;
        $pimple['t_string']  = 'qwerty';
        $pimple['t_int']     = 1;
        $pimple['t_float']   = 1.5;
        $pimple['t_bool']    = true;
        $pimple['t_null']    = null;
        $pimple['t_class']   = new \stdClass();
        $pimple['t_closure'] = function () {
        };

        $pimple['f_array']   = function () {
            return [];
        };
        $pimple['f_string']  = function () {
            return 'qwerty';
        };
        $pimple['f_int']     = function () {
            return 1;
        };
        $pimple['f_float']   = function () {
            return 1.5;
        };
        $pimple['f_bool']    = function () {
            return true;
        };
        $pimple['f_null']    = function () {
        };
        $pimple['f_class']   = function () {
            return new \stdClass();
        };
        $pimple['f_closure'] = function () {
            return function () {
            };
        };
        $pimple['f_pimple']  = function () use ($subPimple) {
            return $subPimple;
        };

        isSame([
            ['name' => 'f_array', 'type' => 'array', 'value' => ''],
            ['name' => 'f_bool', 'type' => 'bool', 'value' => true],
            ['name' => 'f_class', 'type' => 'class', 'value' => 'stdClass'],
            ['name' => 'f_closure', 'type' => 'closure', 'value' => ''],
            ['name' => 'f_float', 'type' => 'float', 'value' => 1.5],
            ['name' => 'f_int', 'type' => 'int', 'value' => 1],
            ['name' => 'f_null', 'type' => 'null', 'value' => ''],
            ['name' => 'f_pimple', 'type' => 'container', 'value' => [
                ['name' => 'f_array', 'type' => 'array', 'value' => ''],
                ['name' => 't_array', 'type' => 'array', 'value' => ''],
            ]],
            ['name' => 'f_string', 'type' => 'string', 'value' => 'qwerty'],
            ['name' => 't_array', 'type' => 'array', 'value' => ''],
            ['name' => 't_bool', 'type' => 'bool', 'value' => true],
            ['name' => 't_class', 'type' => 'class', 'value' => 'stdClass'],
            ['name' => 't_closure', 'type' => 'null', 'value' => ''],
            ['name' => 't_float', 'type' => 'float', 'value' => 1.5],
            ['name' => 't_int', 'type' => 'int', 'value' => 1],
            ['name' => 't_null', 'type' => 'null', 'value' => ''],
            ['name' => 't_pimple', 'type' => 'container', 'value' => [
                ['name' => 'f_array', 'type' => 'array', 'value' => ''],
                ['name' => 't_array', 'type' => 'array', 'value' => ''],
            ]],
            ['name' => 't_string', 'type' => 'string', 'value' => 'qwerty'],
        ], $this->_toJson($dumper->dumpPimple($pimple)));
    }

    public function testAppend()
    {
        $pimple = new Container();
        $dumper = new PimpleDumper();

        $pimple['n_2'] = 2;
        $pimple['n_1'] = 1;
        $pimple['n_3'] = 3;

        $dumper->dumpPimple($pimple);

        $pimple['n_0'] = 0;
        $pimple['n_4'] = 4;

        isSame([
            ['name' => 'n_0', 'type' => 'int', 'value' => 0],
            ['name' => 'n_1', 'type' => 'int', 'value' => 1],
            ['name' => 'n_2', 'type' => 'int', 'value' => 2],
            ['name' => 'n_3', 'type' => 'int', 'value' => 3],
            ['name' => 'n_4', 'type' => 'int', 'value' => 4],
        ], $this->_toJson($dumper->dumpPimple($pimple, true)));
    }

    public function testAutodump()
    {
        $pimple = new Container();
        $dumper = new PimpleDumper();

        $pimple->register($dumper);

        $pimple['auto_2'] = 2;
        $pimple['auto_1'] = 1;

        //$dumper->__destruct();
        unset($dumper);

        isSame([
            ['name' => 'auto_1', 'type' => 'int', 'value' => 1],
            ['name' => 'auto_2', 'type' => 'int', 'value' => 2],
        ], $this->_toJson(PROJECT_ROOT . '/pimple.json'));
    }

    public function testPhpstorm()
    {
        $pimple = new Container();
        $dumper = new PimpleDumper();

        $pimple['f_class'] = function () {
            return new \stdClass();
        };

        isFile($dumper->dumpPhpstorm($pimple));
    }

    /**
     * @param string $file
     * @return array
     */
    protected function _toJson($file)
    {
        $data = json_decode(file_get_contents($file), true);
        return $data;
    }
}
