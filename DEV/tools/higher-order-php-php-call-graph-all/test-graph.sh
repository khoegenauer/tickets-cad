#!/bin/sh
php ./phpcallgraphall.php CallGraphAll/*.php > test-graph.dot
dot -Tpng test-graph.dot -o test-graph.png
