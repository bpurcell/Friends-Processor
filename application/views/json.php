<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");

echo 'var '.$var_name.' = (' . json_encode($query) . ');';

?>