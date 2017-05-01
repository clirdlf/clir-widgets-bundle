(function($) {
  'use strict';

  var CartoDB_Positron = L.tileLayer('http://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="http://cartodb.com/attributions">CartoDB</a>',
    subdomains: 'abcd',
    maxZoom: 19
  });

  var map = L.map('clir_map', {
    zoom: 4,
    layers: [CartoDB_Positron]
  });

  function onEachFeature(feature, layer) {
    var popupContent = '';
    if (feature.properties && feature.properties.organization) {
      popupContent += '<h2>' + feature.properties.organization + '</h2>';
      popupContent += '<p>' + feature.properties.City + ', ' + feature.properties.StateTerri+'</p>';
    }

    layer.bindPopup(popupContent);
  }

  var markers = L.markerClusterGroup(); // @see https://github.com/Leaflet/Leaflet.markercluster
  var oms = new OverlappingMarkerSpiderfier(map);

  oms.addListener('click', function(marker){
    popup.setContent(marker.desc);
    popup.setLatLng(marker.getLatLng());
    map.openPopup(popup);
  });

  oms.addListener('spiderfy', function(markers){
    map.closePopup();
  });

  // console.log(php_vars.layer);

  var geoJsonLayer = L.geoJson(dlf, { // TODO: pass layer properly @see https://github.com/waynegraham/clir-widgets-bundle/issues/3
    onEachFeature: onEachFeature,
    pointToLayer: function(feature, latlng) {
      return L.marker(latlng);
    }
  });

  markers.addLayer(geoJsonLayer);
  map.addLayer(markers);
  map.fitBounds(markers.getBounds());

})(jQuery);
