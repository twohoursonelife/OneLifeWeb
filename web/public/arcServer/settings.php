<?php

$mainServerName = "play.twohoursonelife.com";

require '../config.php';

// change on live server
$sharedSecret = $sharedGameServerSecret;


$sequenceNumberFile = "/tmp/arcServerSequenceNumber.txt";


$secondsPerYearFile = "/tmp/arcServerSecondsPerYear.txt";


$lastArcEndTimeFile = "/tmp/arcServerLastArcEndTime.txt";

$lastArcStartTimeFile = "/tmp/arcServerLastArcStartTime.txt";


$arcLogFile = "/tmp/arcServerLog.txt";

?>