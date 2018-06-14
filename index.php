<!DOCTYPE html>
<html>
<head>
  <title>A Watershed Walk - map</title>
  <link rel="shortcut icon" href="http://www.loughrigg.org/watershed/watershed.ico" />

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

  <script type="text/javascript" src="http://openspace.ordnancesurvey.co.uk/osmapapi/openspace.js?key=CF171E7480B97DC0E0405F0AC86014BC"></script>
  <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
  <script src="geo.js"></script>
  <script src="latlon.js"></script>
  <script src="osgridref.js"></script>
  <script src="wgs_airy.js"></script>

  <style type="text/css">
  body
	{
	  margin: 0px;
	}

  .olControlScaleLine
  {
    margin-bottom: 30px;
    font-size: 14px;
    font-family: Verdana, sans-serif;
    background-color: #eee;
    padding: 8px;
    opacity: 0.8;
  }
  .olControlScaleLineTop, .olControlScaleLineBottom
  {
    font-size: 14px;
  }

	#watershedHeader
	{
		position: relative;
		top: 0em;
		width: 100%;
		height: 18px;
		background-color: #809655;
		color: white;
		font-size: 11px;
		font-family: Verdana, Geneva, sans-serif;
		font-weight: 900;
	}

	#watershedHeader div ul
	{
		margin: 0px;
		margin-left: 1.5em;
		-webkit-padding-start: 0px;
	}

	#watershedHeader div ul li
	{
		display:inline;
		padding-right: 2em;
		text-align: -webkit-match-parent;
		line-height: 15px;
		margin-left: 1.5em;
	}

	#watershedHeader div ul li a
	{
		text-decoration: none;
		color: white;
	}

	#mapcontainer { margin-left: 320px; }
  #sidebar { width: 318px; float: left; }

	#awwTitle
	{
		color: #248;
		font-family: "Trebuchet MS",Helvetica,sans-serif;
		font-weight: bold;
		font-size: 2.0em;
		padding: 1.1em 0.2em 0.8em 0.5em;
	}

	#sidecontent
	{
		margin: 0.2em 0.6em 0.8em 0.6em;
		font-family: Verdana, Geneva, sans-serif;
	}

	.sidedescr
	{
		font-size: 0.7em;
	}

	.todocompl
	{
		line-height: 1.8em;
	}

  .olPopupContent
  {
		font-family: Verdana, Geneva, sans-serif;
		font-size: 0.8em;
  }
  </style>

</head>
<body>

  <?php include "commonheader.html"; ?>

  <div id="sidebar">
	<div id=awwTitle>A Watershed Walk</div>
	<div id="sidecontent">
		<p class="sidedescr">Starting at Dunnet Head, where the Atlantic Ocean meets the North Sea on the north coast of Scotland</p>
		<p class="sidedescr">Finishing at Leathercote Point, where the Channel meets the North Sea at the south-east corner of England</p>
		<p class="sidedescr">Following the line of the watershed, being the drainage divide between the waters flowing towards the Atlantic Ocean (and its arms the Irish Sea and the Channel) and the waters flowing towards the North Sea</p>
		<div class=todocompl>To do: <span id="routeLenToDo"></span> km</div>
		<div class=todocompl>Done: <span id="routeLenCompl"></span> km</div>
	</div>
  </div>
  <div id="mapcontainer">
    <div id="mapdiv"></div>
  </div>

  <script type="text/javascript" src="map.js"></script>

  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

</body>
</html>
