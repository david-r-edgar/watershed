<!DOCTYPE html>
<html>
<head>
  <title>A Watershed Walk - map</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

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

  #maincontainer
  {
    padding-right: 0px;
    padding-left: 0px;
  }
  #maincontainer>.row
  {
    margin-right: 0px;
    margin-left: 0px;
  }
  .navbar-static-top
  {
    margin-bottom: 0px;
    min-height: inherit;
    border-bottom-width: 0px;
  }

  .navbar-static-top .container-fluid
  {
    padding: 0px;
  }

  #mapcontainer
  {
    padding: 0px;
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

  .awwTitle
	{
		color: #248;
		font-family: "Trebuchet MS",Helvetica,sans-serif;
	}

	.sidecontent
	{
		margin: 0.2em 0em 0.8em 0em;
		font-family: Verdana, Geneva, sans-serif;
	}

	.sidedescr
	{
		font-size: 0.9em;
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

  #infoModalButton {
    position: fixed;
    z-index: 900;
    right: 14px;
    top: 20px;
    background-color: #809655;
    border-radius: 100%;
  }
  #infoModalButton button {
    color: white;
  }

  @media screen and (max-width: 767px) {
    #sidebar {
      display: none;
    }
  }
  @media screen and (min-width: 768px) {
    #infoModalButton {
      display: none;
    }
  }
  </style>

</head>
<body>

  <nav class="navbar navbar-default navbar-static-top">
    <div class="container-fluid">
      <?php include "commonheader.html"; ?>
    </div>
  </nav>

  <div id="maincontainer" class="container-fluid">

    <div id=infoModalButton>
      <button type="button" class='btn btn-link'>
        <span aria-hidden="true">&#x24d8;</span>
      </button>
    </div>

    <div class="row">
      <div id="mapcontainer" class="col col-sm-9 order-sm-2 col-xs-12 col-12" style="">
        <div id="mapdiv"></div>
      </div>
      <div id="sidebar" class="col col-sm-3 order-sm-1 col-xs-12 col-12">
        <h3 class="awwTitle">A Watershed Walk</h3>
        <div class="sidecontent">
          <p class="sidedescr">Starting at Dunnet Head, where the Atlantic Ocean meets the North Sea on the north coast of Scotland</p>
          <p class="sidedescr">Finishing at Leathercote Point, where the Channel meets the North Sea at the south-east corner of England</p>
          <p class="sidedescr">Following the line of the watershed, being the drainage divide between the waters flowing towards the Atlantic Ocean (and its arms the Irish Sea and the Channel) and the waters flowing towards the North Sea</p>
          <div class=todocompl>To do: <span class="routeLenToDo"></span> km</div>
          <div class=todocompl>Done: <span class="routeLenCompl"></span> km</div>
        </div>
      </div>
    </div>

  </div>

  <div id=summaryModal class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-body">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h3 class="modal-title">A Watershed Walk</h3>
          <p class="sidedescr">Starting at Dunnet Head, where the Atlantic Ocean meets the North Sea on the north coast of Scotland</p>
          <p class="sidedescr">Finishing at Leathercote Point, where the Channel meets the North Sea at the south-east corner of England</p>
          <p class="sidedescr">Following the line of the watershed, being the drainage divide between the waters flowing towards the Atlantic Ocean (and its arms the Irish Sea and the Channel) and the waters flowing towards the North Sea</p>
          <div class=todocompl>To do: <span class="routeLenToDo"></span> km</div>
          <div class=todocompl>Done: <span class="routeLenCompl"></span> km</div>
        </div>
      </div>
    </div>
  </div>

  <script src="map.js"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

  <script>
    if (window.innerWidth < 768) {
      $('#summaryModal').modal('show')
    }
    $('#infoModalButton').click(function (){
      $('#summaryModal').modal('toggle')
    })
  </script>

</body>
</html>
