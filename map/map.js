var lastUpdLbrt = [0,0,0,0];

var zoom=0;
var gotWatersources = false;
var gotIssues = false;

var markerList = [];

var wpIconSize = new OpenLayers.Size(14, 20);
var wpIconOffset = new OpenLayers.Pixel(-7, -18);
var wpIconInfoWindowAnchor = new OpenLayers.Pixel(9, 18);

function getWpIcon() {
  var iconUrl = "transpSP.png";
  var wpIcon = new OpenSpace.Icon(iconUrl, wpIconSize, wpIconOffset,
                                      null, wpIconInfoWindowAnchor);
  return wpIcon;
}

var issuesIconSize = new OpenLayers.Size(21, 30);
var issuesIconOffset = new OpenLayers.Pixel(-10, -30);
var issuesIconInfoWindowAnchor = new OpenLayers.Pixel(16, 13);

function getIssuesIcon() {
  var iconUrl = "issue.png";
  var issuesIcon = new OpenSpace.Icon(iconUrl, issuesIconSize, issuesIconOffset,
                                        null, issuesIconInfoWindowAnchor);
  return issuesIcon;
}

var watersourcesIconSize = new OpenLayers.Size(19, 28);
var watersourcesIconOffset = new OpenLayers.Pixel(-9, -28);
var watersourcesIconInfoWindowAnchor = new OpenLayers.Pixel(16, 9);

function getWatersourcesIcon() {
  var iconUrl = "watersource.png";
  var watersourcesIcon = new OpenSpace.Icon(iconUrl, watersourcesIconSize,
                                            watersourcesIconOffset, null,
                                            watersourcesIconInfoWindowAnchor);
  return watersourcesIcon;
}

function getPopupBoxText(posE, posN, heading1, description, prefix) {
  var osGR = new OsGridRef(posE, posN);
  var osLL = OsGridRef.osGridToLatLong(osGR);

  var wgs84LL = CoordTransform.convertOSGB36toWGS84(osLL);
  var geohackUrl = "http://toolserver.org/~geohack/geohack.php?pagename="
              + heading1 + "&params=" + wgs84LL._lat + "_N_"
              + wgs84LL._lon + "_E_region:GB_type:landmark";
  var bingUrl = "https://www.bing.com/maps/?&cp="
              + wgs84LL._lat + "~" + wgs84LL._lon + "&lvl=15&sty=s";

  var txt = "<b>";
  if (typeof prefix == "string" && prefix.length > 0) {
    txt += prefix;
  }
  txt += heading1 + "</b>\r\n<br>\r\n";
  if (typeof description == "string" && description.length > 0) {
    txt += description + "\r\n<br>\r\n";
  }
  txt += "<p>\r\ngrid ref: " + osGR.easting + ", " + osGR.northing + "</p>\r\n"
  txt += "<ul>\r\n<li><a href=\"" + geohackUrl
         + "\" target='_blank'>geohack map sources</a></li>\r\n<li>"
         + "<a href=\"" + bingUrl
         + "\" target='_blank'>bing OS 1:25,000</a></li></ul>\r\n";
  return txt;
}

function handleMoveend() {
  if (map.zoom < 2) {
    map.clearMarkers();
    markerList = [];
  }

  if (map.zoom >= 4) {
    var oBnds = map.getExtent();
    var lbrt = oBnds.toArray();
    if ((Math.abs(lbrt[0] - lastUpdLbrt[0]) > 30000)
      || (Math.abs(lbrt[1] - lastUpdLbrt[1]) > 30000)
      || (Math.abs(lbrt[2] - lastUpdLbrt[2]) > 30000)
      || (Math.abs(lbrt[3] - lastUpdLbrt[3]) > 30000)) {
      var url = "api/waypoints/markers?xl=" + (lbrt[0] - 30000) + "&xr=" + (lbrt[2] + 30000) +
              "&yb=" + (lbrt[1] - 30000) + "&yt=" + (lbrt[3] + 30000);
      $.ajax({
        url: url,
        dataType: 'json',
        success: markersCallback
      });

      lastUpdLbrt = lbrt;
    }
    defaultMarkerLayer.setVisibility(true);
  } else {
    defaultMarkerLayer.setVisibility(false);
  }

  if (map.zoom >= 5) {
    if (!gotIssues) {
      $.ajax({
        url: "api/waypoints/issues",
        dataType: 'json',
        success: issuesCallback
      });
      gotIssues = true;
    }
    issuesLayer.setVisibility(true);
  } else {
    //hide issues layer
    issuesLayer.setVisibility(false);
  }

  if (map.zoom >= 4) {
    if (!gotWatersources) {
      $.ajax({
        url: "api/waypoints/watersources",
        dataType: 'json',
        success: watersourcesCallback
      });
      gotWatersources = true;
    }
    watersourcesLayer.setVisibility(true);
  } else {
      //hide watersources layer
      watersourcesLayer.setVisibility(false);
  }
}

function replaceRoute(points, alreadyComplete) {
  //kill the existing route (removeFeatures()?)

  var lineString = new OpenLayers.Geometry.LineString(points);

  var mapProj = map.getProjectionObject();
  var dist = lineString.getGeodesicLength(mapProj) / 1000;
  if (true == alreadyComplete) {
    $(".routeLenCompl").text(dist.toFixed(1));
  } else {
    $(".routeLenToDo").text(dist.toFixed(1));
  }

  var line_style = {strokeColor: "#AA0099", strokeOpacity: 1, strokeWidth: 3};
  if (true == alreadyComplete) {
    line_style = {strokeColor: "#33DD44", strokeOpacity: 1, strokeWidth: 3};
  }
  var lineFeature = new OpenLayers.Feature.Vector(lineString, null, line_style);
  defaultVectorLayer.addFeatures([lineFeature]);
}

function addMarker(e, n, txt) {
  for (m = 0; m < markerList.length; m++) {
    var cm = markerList[m];

    if ((cm.lonlat.lon == e) && (cm.lonlat.lat == n)) {
      markerList.splice(m, 1);
      m --;
      map.removeMarker(cm);
    }
  }

  var pos = new OpenSpace.MapPoint(e, n);
  var m1 = map.createMarker(pos, getWpIcon(), txt);
  markerList.push(m1);
}



function markersCallback(data) {
  var points = [];
  for (p in data) {
    if ((!isNaN(data[p].E) && !isNaN(data[p].N))
        && (currentPos.E != data[p].E && currentPos.N != data[p].N)) {
      var popupBoxNote =
        (data[p].distSoFar / 1000).toFixed(1) + ' km from Dunnet Head<br>' +
        (data[p].distRemaining / 1000).toFixed(1) + ' km to Leathercote Point<br>' +
        data[p].Note;
      var popupText = getPopupBoxText(data[p].E, data[p].N,
        data[p].Name, popupBoxNote);
      addMarker(data[p].E, data[p].N, popupText);
    }
  }
}

function issuesCallback(data) {
  var points = [];
  for (p in data) {
    if (!isNaN(data[p].E) && !isNaN(data[p].N)) {
      var txt = getPopupBoxText(data[p].E, data[p].N,
        data[p].Place, data[p].Issue);
      var pos = new OpenSpace.MapPoint(data[p].E, data[p].N);
      var m1 = map.createMarker(pos, getIssuesIcon(), txt);
      defaultMarkerLayer.removeMarker(m1);
      issuesLayer.addMarker(m1);
    }
  }
}

function watersourcesCallback(data) {
  var points = [];
  for (p in data) {
    if (!isNaN(data[p].E) && !isNaN(data[p].N)) {
      var txt = getPopupBoxText(data[p].E, data[p].N,
        data[p].Place, data[p].Source);
      var pos = new OpenSpace.MapPoint(data[p].E, data[p].N);
      var m1 = map.createMarker(pos, getWatersourcesIcon(), txt);
      defaultMarkerLayer.removeMarker(m1);
      watersourcesLayer.addMarker(m1);
    }
  }
}

function recvCoordsDataCallback(data, alreadyComplete) {
  var points = [];
  for (p in data) {
    if (!isNaN(data[p].E) && !isNaN(data[p].N)) {
      var pt = new OpenLayers.Geometry.Point(data[p].E, data[p].N);
      points.push(pt);
    }
  }

  replaceRoute(points, alreadyComplete);
}

function recvCoordsDataCallbackCompl(data) {
  recvCoordsDataCallback(data, true);
}

function recvCoordsDataCallbackToDo(data) {
  recvCoordsDataCallback(data, false);
}

function resizeElementHeight(element) {
  var height = 0;
  var body = window.document.body;
  if (window.innerHeight) {
    height = window.innerHeight;
  } else if (body.parentElement.clientHeight) {
    height = body.parentElement.clientHeight;
  } else if (body && body.clientHeight) {
    height = body.clientHeight;
  }
  element.style.height = ((height - $(element).offset().top) + "px");
}

var currentPos = {"E":0,"N":0};

function doneuptoCallback(data) {
  if (isNaN(data.E) || isNaN(data.N)) {
    return;
  }
  currentPos = data;

  var size = new OpenLayers.Size(36, 36);
  var offset = new OpenLayers.Pixel(-16, -33);
  var infoWindowAnchor = new OpenLayers.Pixel(24, 16);
  var iconUrl = "currentHiker.png";
  var hikerIcon = new OpenSpace.Icon(iconUrl, size, offset, null, infoWindowAnchor);

  var txt = getPopupBoxText(currentPos.E, currentPos.N,
              currentPos.Name, currentPos.Note, "<i>Current position: </i>");
  var posStart = new OpenSpace.MapPoint(currentPos.E,currentPos.N);
  var mStart = map.createMarker(posStart, hikerIcon, txt);
  defaultMarkerLayer.removeMarker(mStart);
  majorMarkers.addMarker(mStart);
}

var routeStartPos;
var routeFinishPos;

function majorMarkersCallback(data) {
  var points = [];

  var size = new OpenLayers.Size(32, 32);
  var offset = new OpenLayers.Pixel(-16, -32);
  var infoWindowAnchor = new OpenLayers.Pixel(18, 30);
  var iconUrl = "yellowMarker.png";
  var yellowIcon = new OpenSpace.Icon(iconUrl, size, offset, null, infoWindowAnchor);
  var yellowIcon2 = new OpenSpace.Icon(iconUrl, size, offset, null, infoWindowAnchor);

  routeStartPos = data[0];
  routeFinishPos = data[1];
  if (!isNaN(routeStartPos.E) && !isNaN(routeStartPos.N)) {
    var txt = getPopupBoxText(routeStartPos.E, routeStartPos.N,
                routeStartPos.Name, null, "<u>Start</u>: ");
    var posStart = new OpenSpace.MapPoint(routeStartPos.E,routeStartPos.N);
    var mStart = map.createMarker(posStart, yellowIcon, txt);

    defaultMarkerLayer.removeMarker(mStart);
    majorMarkers.addMarker(mStart);
  }
  if (!isNaN(routeFinishPos.E) && !isNaN(routeFinishPos.N)) {
    var txt = getPopupBoxText(routeFinishPos.E, routeFinishPos.N,
                 routeFinishPos.Name, null, "<u>Finish</u>: ");
    var posFinish = new OpenSpace.MapPoint(routeFinishPos.E,routeFinishPos.N);
    var mFinish = map.createMarker(posFinish, yellowIcon2, txt);
    defaultMarkerLayer.removeMarker(mFinish);
    majorMarkers.addMarker(mFinish);
  }

  $.ajax({
    url: 'api/waypoints/currentposition',
    dataType: 'json',
    success: doneuptoCallback
  });
}

var addMajorMarkers = function() {
  $.ajax({
    url: "api/waypoints/majormarkers",
    dataType: 'json',
    success: majorMarkersCallback
  });
}

var loadRoute = function() {
  $.ajax({
    url: "api/waypoints/done",
    dataType: 'json',
    success: recvCoordsDataCallbackCompl
  });

  $.ajax({
    url: "api/waypoints/todo",
    dataType: 'json',
    success: recvCoordsDataCallbackToDo
  });
}

var setupLayers = function() {
  defaultMarkerLayer = map.getMarkerLayer();
  defaultVectorLayer = map.getVectorLayer();
  defaultVectorLayer.setZIndex(100);
  map.addControl(new OpenLayers.Control.LayerSwitcher({'ascending':false}));
    majorMarkers = new OpenLayers.Layer.Markers("Start+Now+Finish");
  map.addLayer(majorMarkers);
    watersourcesLayer = new OpenLayers.Layer.Markers("Major sources");
  map.addLayer(watersourcesLayer);
    issuesLayer = new OpenLayers.Layer.Markers("Issues");
  map.addLayer(issuesLayer);
}

var initMap = function() {
  map = new OpenSpace.Map("mapdiv");
      setupLayers();
  map.setCenter(new OpenSpace.MapPoint(362000, 600000), zoom);
  addMajorMarkers();
  var sl = new OpenLayers.Control.ScaleLine();
  sl.maxWidth = 160;
  map.addControl(sl);

  //map.events.register("zoomend", map, handleZoomend );
  map.events.register("moveend", map, handleMoveend );
}

var start = function() {
  resizeElementHeight($("#mapdiv")[0]);
  initMap();
  loadRoute();
}

$(document).ready(start);
