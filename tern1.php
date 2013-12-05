<?php
/**
 * the_func
 * Insert description here
 *
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function the_func() {
    return FALSE;
    }
if ($temp = the_func()) {
    print __LINE__;
    print $temp;
    }
else {
    print __LINE__;
    }
