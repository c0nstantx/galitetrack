galitetrack
===========

Google Analytics Lite Tracking PHP Class

It's a simple PHP Class that displays the Google Analytics tracking code.
The simplest use of it is the following:

<?php
include 'Gatracking.php';

$ga = new Gatracking('UA-XXXXX-Y');

echo $ga->getTrackingCode();

?>
