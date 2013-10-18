<?php
  /**
   * PHP Call Graph All generates a call graph of all functions and
   * methods used in a set of PHP files. This is the script that can be
   * run from the command-line.
   *
   * PHP Version 5
   *
   * @category  PHP
   * @package   PHP_CallGraphAll
   * @author    Rudolf Olah <omouse@gmail.com>
   * @copyright 2011 Rudolf Olah
   * @license   General Public License (GPL) version 3 or later
   * @link      http://neverfriday.com/?q=project/php_callgraphall
   */

require 'lib/PHPParser/Autoloader.php';
PHPParser_Autoloader::register();
require 'CallGraphAll/DotVisitor.php';
require 'CallGraphAll/Generator.php';

// remove the first command line argument (it's just the script name)
array_shift($argv);
if (count($argv) == 0) {
    die('Requires at least one input path: phpcallgraphall INPUT...' . "\n");
}

$generator = new PHP_CallGraphAll_Generator(new PHP_CallGraphAll_DotVisitor);
$generator->generate($argv);

?>