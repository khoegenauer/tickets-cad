#!/bin/sh
#KHoegenauer mod for testing Tickets
php ./DEV/tools/higher-order-php-php-call-graph-all/phpcallgraphall.php ./*.php > ./DEV/build/test-graph.dot
dot -Tsvg ./DEV/build/test-graph.dot -o ./DEV/build/test-graph.svg
#dot -Tpng ./DEV/build/test-graph.dot -o ./DEV/build/test-graph.png

