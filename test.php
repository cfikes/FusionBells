<?php
function one() {
	two("hello");
}


function two($test) {
	echo $test . " World!";
}


one();

?>