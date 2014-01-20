<!DOCTYPE html>

	<title>Leaflet Search / LayerGroup issue</title>
<body>
	<div id="map" style="width: 600px; height: 400px; position: relative;"></div>
	<script>
		 var Field1 = { "type": "Feature", "properties": {  "popupContent": "Field 1" }, "geometry": {  "type": "Point",  "coordinates": [-105.2, 39.2] } };
		 var Field2 = { "type": "Feature", "properties": {  "popupContent": "Field 2" }, "geometry": {  "type": "Point",  "coordinates": [-104.2, 40.2] } };
		 var Field3 = { "type": "Feature", "properties": {  "popupContent": "Field 3" }, "geometry": {  "type": "Point",  "coordinates": [-105, 39] } };
		 var Field4 = { "type": "Feature", "properties": {  "popupContent": "Field 4" }, "geometry": {  "type": "Point",  "coordinates": [-104, 40] } };

		var map = L.map('map').setView([39.7, -105], 8);

		L.tileLayer('http://{s}.tile.cloudmade.com/{key}/22677/256/{z}/{x}/{y}.png', {
			key: 'BC9A493B41014CAABB98F0471D759707'
			}).addTo(map);

		var Cloud = "http://{s}.tile.cloudmade.com/BC9A493B41014CAABB98F0471D759707/{styleId}/256/{z}/{x}/{y}.png";
		var minimal = L.tileLayer(Cloud, { styleId: 22677 });
		var BaseMaps = { "minimal": minimal };

		var baseballIcon = L.icon({
			iconUrl: 'http://leafletjs.com/examples/baseball-marker.png',
			iconSize: [32, 37],
			iconAnchor: [16, 37],
			popupAnchor: [0, -28]
		});

		function onEachFeature(feature, layer) {
			var popupContent = "";
			if (feature.properties && feature.properties.popupContent) {
				popupContent += feature.properties.popupContent;
			}
			layer.bindPopup(popupContent);
		}

		var Field1Layer = L.geoJson(Field1, {
			pointToLayer: function(feature, latlng) {
				return L.marker(latlng, {
					icon: baseballIcon
				});
			},
			onEachFeature: onEachFeature
		});

		var Field2Layer = L.geoJson(Field2, {
			pointToLayer: function(feature, latlng) {
				return L.marker(latlng, {
					icon: baseballIcon
				});
			},
			onEachFeature: onEachFeature
		});

		var Field3Layer = L.geoJson(Field3, {
			pointToLayer: function(feature, latlng) {
				return L.marker(latlng, {
					icon: baseballIcon
				});
			},
			onEachFeature: onEachFeature
		});

		var Field4Layer = L.geoJson(Field4, {
			pointToLayer: function(feature, latlng) {
				return L.marker(latlng, {
					icon: baseballIcon
				});
			},
			onEachFeature: onEachFeature
		});

		var FieldGroup = L.layerGroup([Field3Layer, Field4Layer]);

		var OverlayMaps = {
			"Field Group": FieldGroup,
				"Field 1": Field1Layer,
				"Field 2": Field2Layer
		}
		L.control.layers(BaseMaps, OverlayMaps).addTo(map);
	</script>
</body>