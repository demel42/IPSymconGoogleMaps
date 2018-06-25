<?php

require_once __DIR__ . '/../libs/common.php';  // globale Funktionen

class GoogleMaps extends IPSModule
{
    use GoogleMapsCommon;

    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyString('api_key', '');
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $api_key = $this->ReadPropertyString('api_key');

        $this->SetStatus($api_key == '' ? 104 : 102);
    }

public function GenerateMap(string $title, $origin, $marker, $polyline, $options)
{
    $api_key = $this->ReadPropertyString('api_key');

    $map_options = isset($options['map_options']) ? $options['map_options'] : '';

    $marker_points = isset($marker['points']) ? $marker['points'] : '';
    $marker_options = isset($marker['marker_options']) ? $marker['marker_options'] : '';
    $infowindow_options = isset($marker['infowindow_options']) ? $marker['infowindow_options'] : '';

    $polyline_points = isset($polyline['points']) ? $polyline['points'] : '';
    $polyline_options = isset($polyline['polyline_options']) ? $polyline['polyline_options'] : '';

    $s = '
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no"/>
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>

<style type="text/css">
    html { height: 100% }
    body { height: 100%; margin: 0; padding: 0 }
    #map-canvas { height: 100% }
</style>

<title>' . $title . '</title>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=' . $api_key . '"></script>
<script type="text/javascript">
    function initialize() {
        var origin = new google.maps.LatLng(' . json_encode($origin) . ');
        var mapOptions = ' . json_encode($map_options) . ';
        mapOptions.center = origin;
        var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);

        var infowindowOptions = ' . json_encode($infowindow_options) . ';
        var infowindow = new google.maps.InfoWindow();
';
    if ($marker_points != '') {
        $s .= '
        var markerLocations = ' . json_encode($marker_points) . ';
        for(i = 0; i < markerLocations.length; i++) {
            var position = new google.maps.LatLng(markerLocations[i]);
            var markerOptions = ' . json_encode($marker_options) . ';
            if (markerLocations[i]["options"])
            	markerOptions = markerLocations[i]["options"];
            markerOptions.position = position;
            markerOptions.map = map;
            var marker = new google.maps.Marker(markerOptions);
            google.maps.event.addListener(marker, "click", (function(marker, i) {
                    return function() {
                        if (markerLocations[i]["info"]) {
                            infowindow.setContent(markerLocations[i]["info"]);
                            infowindow.setOptions(infowindowOptions);
                            infowindow.open(map, marker);
                        }
                    }
                }) (marker, i));
        }
';
    }

    if ($polyline_points != '') {
        $s .= '
        var polylineLocationList = ' . json_encode($polyline_points) . ';
        var polylineOptions = ' . json_encode($polyline_options) . ';
        polylineOptions.path = polylineLocationList;
        var polyline = new google.maps.Polyline(polylineOptions);
        polyline.setMap(map);
';
    }

    $s .= '
    }

    google.maps.event.addDomListener(window, "load", initialize);
</script>

</head> 
<body>
    <div id="map-canvas"/>
</body>
</html>
';

    return $s;
}

}
