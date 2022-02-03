import config from './config.js'
import * as utils from './utils.js'
import * as templater from './templater.js'
import { getIcon } from './icons.js'

const serviceUrl = 'https://api.os.uk/maps/raster/v1/zxy'

let map
const overlayLayers = {}

function initBaseLayers () {
  return {
    light: L.tileLayer(`${serviceUrl}/Light_27700/{z}/{x}/{y}.png?key=${config.apiKey}`, { maxZoom: 20 }),
    outdoor: L.tileLayer(`${serviceUrl}/Outdoor_27700/{z}/{x}/{y}.png?key=${config.apiKey}`, { maxZoom: 20 }),
    road: L.tileLayer(`${serviceUrl}/Road_27700/{z}/{x}/{y}.png?key=${config.apiKey}`, { maxZoom: 20 }),
    leisure: L.tileLayer(`${serviceUrl}/Leisure_27700/{z}/{x}/{y}.png?key=${config.apiKey}`, { maxZoom: 20 })
  }
}

// the UI radio selector element for the different base layers
const setupLayerControl = function(baseLayers, overlayLayers) {
  const baseMaps = {
    'Leisure': baseLayers.leisure,
    'Light': baseLayers.light,
    'Outdoor': baseLayers.outdoor,
    'Road': baseLayers.road,
  }
  const overlayMaps = {
    'Start+Now+Finish': overlayLayers.majorMarkers,
    'Waypoints': overlayLayers.wptLayer,
    'Major sources': overlayLayers.waterSources,
    'Issues': overlayLayers.issues
  }
  L.control.layers(baseMaps, overlayMaps).addTo(map)
}

const initMap = function(rootElementId) {
  const crs = utils.getProjectionCRS()

  const baseLayers = initBaseLayers()

  const mapOptions = {
    crs,
    layers: [ baseLayers.outdoor ],
    minZoom: 0,
    maxZoom: 9,
    center: utils.transformToLatLong([ 368727, 534316 ]),
    zoom: 0,
    maxBounds: [
      utils.transformToLatLong([ -238375.0, 0.0 ]),
      utils.transformToLatLong([ 900000.0, 1376256.0 ])
    ],
    attributionControl: false
  }
  map = L.map(rootElementId, mapOptions)

  overlayLayers.wptLayer = L.layerGroup()
  overlayLayers.majorMarkers = L.layerGroup()
  overlayLayers.waterSources = L.layerGroup()
  overlayLayers.issues = L.layerGroup()

  setupLayerControl(baseLayers, overlayLayers)
  L.control.scale().addTo(map)

  map.on('moveend', (ev) => {
    handleMapMove(overlayLayers)
  })
}

// triggered at the end of every map move and map zoom
function handleMapMove(overlayLayers) {
  const zoom = map.getZoom()

  // console.log('zoom', zoom)

  if (zoom >= 3) {
    showWaypointsWithinBounds(map.getBounds(), overlayLayers.wptLayer)
    overlayLayers.wptLayer.addTo(map)
  } else {
    overlayLayers.wptLayer.removeFrom(map)
  }

  if (zoom >= 4) {
    overlayLayers.waterSources.addTo(map)
  } else {
    overlayLayers.waterSources.removeFrom(map)
  }

  if (zoom >= 5) {
    overlayLayers.issues.addTo(map)
  } else {
    overlayLayers.issues.removeFrom(map)
  }
}

function showRouteSection(data, alreadyComplete) {
  const colour = alreadyComplete ? '#33DD44 ': '#AA0099'
  const points = []
  for (const p of data) {
    if (!isNaN(p.E) && !isNaN(p.N)) {
      const coords = utils.transformToLatLong([p.E, p.N])
      points.push(coords)
    }
  }
  L.polyline(points, {color: colour}).addTo(map)
}

const loadRoute = async function() {
  const fetchedDone = await window.fetch('api/waypoints/done')
  const doneJson = await fetchedDone.json()
  showRouteSection(doneJson, true)

  const fetchedTodo = await window.fetch('api/waypoints/todo')
  const todoJson = await fetchedTodo.json()
  showRouteSection(todoJson, false)
}

// last bounds we've shown waypoints for (for 'caching' purposes)
let lastEnws = [0, 0, 0, 0]

async function showWaypointsWithinBounds(bounds, layer) {
  // console.log({bounds})

  const neCoords = utils.transformToOSGB([bounds._northEast.lat, bounds._northEast.lng])
  const swCoords = utils.transformToOSGB([bounds._southWest.lat, bounds._southWest.lng])
  const enws = [...neCoords, ...swCoords]

  if (enws.filter((coordVal, index) => {
    return (Math.abs(coordVal - lastEnws[index]) > 30000)
  }).length > 0) {
    // fetch from a wider area than the bounds of the map
    const url = `api/waypoints/markers?xl=${enws[2] - 30000}&xr=${enws[0] + 30000}&yb=${enws[3] - 30000}&yt=${enws[1] + 30000}`
    const fetchedWpts = await window.fetch(url)
    const wpts = await fetchedWpts.json()
    showWaypoints(wpts, layer)

    lastEnws = enws
  }
}

const majorMarkerLocations = []

function showWaypoints(data, layer) {
  layer.clearLayers() //clear all previous waypoints from the layer
  for (const p of data) {
    // avoid showing regular waypoints in the same location as start/finish/curPos
    if (!majorMarkerLocations.find(e => {return (e[0] === p.E && e[1] === p.N)})) {
      showMarker(p, layer, 'wp', p.Name, p.distSoFar, p.distRemaining, p.Note)
    }
  }
}

function showMajorMarker(pt, layer, distFromStart, distToFinish, prefix) {
  showMarker(pt, layer, 'end', pt.Name, distFromStart, distToFinish, pt.Note, `<u>${prefix}</u>: `)
}

function showMajorMarkers(majorMarkers, layer) {
  showMajorMarker(majorMarkers[0], layer, null, majorMarkers[0].distToFinish * 1000, 'Start')
  majorMarkerLocations.push([majorMarkers[0].E, majorMarkers[0].N])
  showMajorMarker(majorMarkers[1], layer, majorMarkers[1].distFromStart * 1000, null, 'Finish')
  majorMarkerLocations.push([majorMarkers[1].E, majorMarkers[1].N])
}

function showCurPos(curPos, layer) {
  showMarker(curPos, layer, 'curPos', curPos.Name, curPos.distFromStart * 1000, curPos.distToFinish * 1000, curPos.Note, '<i>Current position: </i>')
  majorMarkerLocations.push([curPos.E, curPos.N])
}

function updateInfoPaneDistances(curPos) {
  document.querySelectorAll('.routeLenCompl').forEach(elem => {
    elem.textContent = curPos.distFromStart.toFixed(1)
  })
  document.querySelectorAll('.routeLenToDo').forEach(elem => {
    elem.textContent = curPos.distToFinish.toFixed(1)
  })
}

function showSources(sources, layer) {
  for (const p of sources) {
    showMarker(p, layer, 'watersource', p.Place, p.distFromStart * 1000, p.distToFinish * 1000, p.Source)
  }
}

function showIssues(issues, layer) {
  for (const p of issues) {
    showMarker(p, layer, 'issue', p.Place, p.distFromStart * 1000, p.distToFinish * 1000, p.Issue)
  }
}

function showMarker(p, layer, icon, name, distFromStart, distToFinish, description, prefix) {
  if (isNaN(p.E) || isNaN(p.N)) return
  const latLng = utils.transformToLatLong([p.E, p.N])
  const popupText = templater.getWaypointPopupText([p.E, p.N], latLng, name, distFromStart, distToFinish, description, prefix)
  const markerOptions = { icon: getIcon(icon) }
  const marker = L.marker(latLng, markerOptions).addTo(layer)
  marker.bindPopup(popupText)
}

const loadMajorMarkers = async function() {
  const fetchedMajorMarkers = await window.fetch('api/waypoints/majormarkers')
  const majorMarkersJson = await fetchedMajorMarkers.json()
  showMajorMarkers(majorMarkersJson, overlayLayers.majorMarkers)
  overlayLayers.majorMarkers.addTo(map)
}

const loadCurPos = async function () {
  const fetchedCurPos = await window.fetch('api/waypoints/currentposition')
  const curPosJson = await fetchedCurPos.json()
  showCurPos(curPosJson, overlayLayers.majorMarkers)
  updateInfoPaneDistances(curPosJson)
}

const loadWaterSources = async function () {
  const fetched = await window.fetch('api/waypoints/watersources')
  const json = await fetched.json()
  showSources(json, overlayLayers.waterSources)
}

const loadIssues = async function () {
  const fetched = await window.fetch('api/waypoints/issues')
  const json = await fetched.json()
  showIssues(json, overlayLayers.issues)
}

const start = function() {
  initMap('map')
  loadRoute()
  loadMajorMarkers()
  loadCurPos()
  loadWaterSources()
  loadIssues()
}

function ready() {
  if (document.readyState != 'loading') {
    start()
  } else {
    document.addEventListener('DOMContentLoaded', start)
  }
}

ready()
