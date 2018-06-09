<?php

$subroute = 0;

if (isset($_GET['r']))
{
    $subroute = $_GET['r'];
}

$datafile = "data" . $subroute . ".json";

readfile ($datafile);
?>
