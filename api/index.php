<?php

require 'flight/Flight.php';
require 'waypoints.php';

Flight::route('/', function(){
    echo '';
});

// changes - now returns full points as json
Flight::route('GET /waypoints/done', function() {
  Flight::json(Waypoints::getDone());
});

// changes - now returns full points as json
Flight::route('GET /waypoints/todo', function() {
  Flight::json(Waypoints::getToDo());
});

// changes - now includes distance data in points
Flight::route('GET /waypoints/markers', function() {
  $xl = Flight::request()->query->xl ?: 100000;
  $xr = Flight::request()->query->xr ?: 700000;
  $yb = Flight::request()->query->yb ?: 100000;
  $yt = Flight::request()->query->yt ?: 1000000;

  Flight::json(Waypoints::getMarkers($xl, $xr, $yb, $yt));
});

Flight::route('GET /waypoints/majormarkers', function() {
  Flight::json(Waypoints::getMajorMarkers());
});

Flight::route('GET /waypoints/watersources', function() {
  Flight::json(Waypoints::getWaterSources());
});

Flight::route('GET /waypoints/issues', function() {
  Flight::json(Waypoints::getIssues());
});

// changes - now returns just a wpt object, rather than an array containing the wpt object
Flight::route('/waypoints/currentposition', function() {
  Flight::json(Waypoints::getCurrentPosition());
});

Flight::start();

?>
