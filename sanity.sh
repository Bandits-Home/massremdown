#!/bin/bash
 
#massremdown component sanity check

function zipit() {
	:
}

#~ Include general library (should go in all sanity scripts.)
if [ ! -f /usr/local/nagiosxi/html/includes/components/sanitychecks/sanitylib.sh ];then
    echo "Sanity Checks Component not installed"
    exit 1
else 
    . /usr/local/nagiosxi/html/includes/components/sanitychecks/sanitylib.sh
fi

do_these_files_exist $COMPONENTS/massremdown/massremdown.inc.php \
	$COMPONENTS/massremdown/mass_rem.php

is_component $COMPONENTS/massremdown/massremdown.inc.php 

print_results
