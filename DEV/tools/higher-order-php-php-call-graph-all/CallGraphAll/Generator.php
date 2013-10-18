<?php
  /**
   * The PHP_CallGraphAll_Generator objects generates a call graph of
   * all functions and methods used in a set of PHP files.
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

  /**
   * Generates a call graph of all functions and methods used in a set
   * of PHP files.
   *
   * @category  PHP
   * @package   PHP_CallGraphAll
   * @author    Rudolf Olah <omouse@gmail.com>
   * @copyright 2011 Rudolf Olah
   * @license   General Public License (GPL) version 3 or later
   * @link      http://neverfriday.com/?q=project/php_callgraphall
   */
class PHP_CallGraphAll_Generator
{
    /**
     * The visitor used to visit each node.
     *
     * @var PHPParser_NodeVisitorInterface
     */
    public $visitor;

    /**
     * The PHP parser
     *
     * @var PHPParser_Parser
     */
    private $_parser;

    /**
     * Creates a new Generator that uses the given vistor object.
     *
     * @param PHPParser_NodeVisitorInterface $visitor Visitor class
     * used to visit each node during generation of the graph
     */
    function __construct(PHPParser_NodeVisitorInterface $visitor)
    {
        $this->visitor = $visitor;
        $this->_parser = new PHPParser_Parser;
    }

    /**
     * Generates Graphviz commands based on the contents of a PHP file
     * and writes them to standard output.
     *
     * @param string                  $path      The PHP filename
     * @param PHPParser_NodeTraverser $traverser The node traverser
     * visits each node
     *
     * @return void
     */
    function _generateCommandsForFile($path,
                                      PHPParser_NodeTraverser $traverser)
    {
        echo "/* $path */\n";
        $this->_generateCommands(file_get_contents($path), $traverser,
                                 basename($path));
    }

    /**
     * Generates Graphviz commands based on the PHP code string and
     * writes them to standard output.
     *
     * @param string                  $input     The PHP code

     * @param PHPParser_NodeTraverser $traverser The node traverser visits each
     * node
     * @param string                  $label     Optional label that can be given
     * to the graph
     *
     * @return void
     */
    function _generateCommands($input, PHPParser_NodeTraverser $traverser,
                               $label=null)
    {
        echo "subgraph {\n";
        if ($label) {
            echo "label = \"$label\"\n";
        }
        try {
            $stmts = $this->_parser->parse(new PHPParser_Lexer($input));
            $traverser->traverse($stmts);
        } catch (PHPParser_Error $e) {
            echo '/* Parse Error: ' . $e->getMessage() . "*/\n";
        }
        echo "}\n";
    }

    /**
     * Traverses a PHP file and writes a graphviz dot file to standard
     * output.
     *
     * @param array $inputFilePaths Array of strings that are input file pathnames
     *
     * @return void
     */
    function generate(array $inputFilePaths)
    {
        echo 'digraph g {', "\n";
        $traverser = new PHPParser_NodeTraverser;
        $traverser->addVisitor($this->visitor);
        foreach ($inputFilePaths as $path) {
            $this->_generateCommandsForFile($path, $traverser);
        }
        echo "}\n";
    }
}
