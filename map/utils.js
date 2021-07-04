export const getProjectionCRS = function() {
  // Setup the EPSG:27700 (British National Grid) projection
  const crs = new L.Proj.CRS('EPSG:27700', '+proj=tmerc +lat_0=49 +lon_0=-2 +k=0.9996012717 +x_0=400000 +y_0=-100000 +ellps=airy +towgs84=446.448,-125.157,542.06,0.15,0.247,0.842,-20.489 +units=m +no_defs', {
    resolutions: [ 896.0, 448.0, 224.0, 112.0, 56.0, 28.0, 14.0, 7.0, 3.5, 1.75 ],
    origin: [ -238375.0, 1376256.0 ]
  })
  return crs
}

// returns WGS84, as: [latitude, longitude]
export const transformToLatLong = function(arr) {
  return proj4('EPSG:27700', 'EPSG:4326', arr).reverse()
}

// returns in OSGB36, as: [easting, northing]
export const transformToOSGB = function(arr) {
  return proj4('EPSG:4326', 'EPSG:27700', arr.reverse())
}
