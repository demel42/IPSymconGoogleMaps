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

        //$this->SetStatus($api_key == '' ? 104 : 102);
        $this->SetStatus(102);
    }

    public function GenerateDynamicMap($map)
    {
        $api_key = $this->ReadPropertyString('api_key');
        if ($api_key == '') {
            LogMessage(__FUNCTION__ . ': GenerateDynamicMap requires a valid API-Key', KL_WARNING);
            return '';
        }

        $url = 'https://maps.googleapis.com/maps/api/js?key=' . $api_key;

        $center = isset($map['center']) ? $map['center'] : '';
        $map_options = isset($map['map_options']) ? $map['map_options'] : '';
        $infowindow_options = isset($map['infowindow_options']) ? $map['infowindow_options'] : '';
        $markers = isset($map['markers']) ? $map['markers'] : '';
        $paths = isset($map['paths']) ? $map['paths'] : '';

        // Kopf
        $html = '
<html>
    <head>
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no"/>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>

        <style type="text/css">
            html { height: 100% }
            body { height: 100%; margin: 0; padding: 0 }
            #map-canvas { height: 100% }
        </style>
    </head>
    <body>
        <div id="map-canvas"/>

        <script type="text/javascript">
            function initialize() {
                var center = new google.maps.LatLng(' . json_encode($center) . ');
                var mapOptions = ' . json_encode($map_options) . ';
                mapOptions.center = center;
                var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);

                var infowindowOptions = ' . json_encode($infowindow_options) . ';
                if (infowindowOptions == "")
                    infowindowOptions = {};
                var infowindow = new google.maps.InfoWindow();
';
        // Karte mit Punkten
        if ($markers != '') {
            foreach ($markers as $marker) {
                $marker_points = isset($marker['points']) ? $marker['points'] : '';
                $marker_options = isset($marker['marker_options']) ? $marker['marker_options'] : '';
                $html .= '
                var markerLocations = ' . json_encode($marker_points) . ';
                for(i = 0; i < markerLocations.length; i++) {
                    var position = new google.maps.LatLng(markerLocations[i]);
                    var markerOptions = ' . json_encode($marker_options) . ';
                    if (markerLocations[i]["marker_options"])
                        markerOptions = markerLocations[i]["marker_options"];
                    if (markerOptions == "")
                        markerOptions = {};
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
        }

        // Karte mit verbundenen Punkten
        if ($paths != '') {
            foreach ($paths as $path) {
                $path_points = isset($path['points']) ? $path['points'] : '';
                $polyline_options = isset($path['polyline_options']) ? $path['polyline_options'] : '';
                $html .= '
                var polylineOptions = ' . json_encode($polyline_options) . ';
                if (polylineOptions == "")
                    polylineOptions = {};
                polylineOptions.path = ' . json_encode($path_points) . ';;
                var polyline = new google.maps.Polyline(polylineOptions);
                polyline.setMap(map);
';
            }
        }

        // Fussbereich
        $html .= '
            }
        </script>
        <script async defer src="https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&callback=initialize"></script>
    </body>
</html>
';

        return $html;
    }

    public function GenerateStaticMap($map)
    {
        $url = 'https://maps.googleapis.com/maps/api/staticmap?key=';

        $api_key = $this->ReadPropertyString('api_key');
        if ($api_key != '') {
            $url .= $api_key;
        }

        if (isset($map['center'])) {
            $lat = number_format($map['center']['lat'], 6, '.', '');
            $lng = number_format($map['center']['lng'], 6, '.', '');
            $url .= '&center=' . rawurlencode($lat . ',' . $lng);
        }

        foreach (['zoom', 'size', 'scale', 'maptype'] as $key) {
            if (isset($map[$key])) {
                $url .= '&' . $key . '=' . rawurlencode($map[$key]);
            }
        }

        if (isset($map['styles'])) {
            $styles = $map['styles'];
            foreach ($styles as $style) {
                $s = '';
                foreach (['feature', 'color'] as $key) {
                    if (isset($style[$key])) {
                        if ($s != '') {
                            $s .= '|';
                        }
                        $s .= $key . ':' . $style[$key];
                    }
                }
                $url .= '&style=' . rawurlencode($s);
            }
        }

        $markers = isset($map['markers']) ? $map['markers'] : '';
		if ($markers != '') {
			foreach ($markers as $marker) {
				$s = '';
				foreach (['color', 'label', 'size'] as $key) {
					if (isset($marker[$key])) {
						if ($s != '') {
							$s .= '|';
						}
						$s .= $key . ':' . $marker[$key];
					}
				}
				if (isset($marker['points'])) {
					$points = $marker['points'];
					foreach ($points as $point) {
						$lat = number_format($point['lat'], 6, '.', '');
						$lng = number_format($point['lng'], 6, '.', '');
						if ($s != '') {
							$s .= '|';
						}
						$s .= $lat . ',' . $lng;
					}
				}
				$url .= '&markers=' . rawurlencode($s);
			}
		}

        $paths = isset($map['paths']) ? $map['paths'] : '';
		if ($paths != '') {
			foreach ($paths as $path) {
				$s = '';
				foreach (['color', 'weight'] as $key) {
					if (isset($path[$key])) {
						if ($s != '') {
							$s .= '|';
						}
						$s .= $key . ':' . $path[$key];
					}
				}
				if (isset($path['points'])) {
					$points = $path['points'];
					foreach ($points as $point) {
						$lat = number_format($point['lat'], 6, '.', '');
						$lng = number_format($point['lng'], 6, '.', '');
						if ($s != '') {
							$s .= '|';
						}
						$s .= $lat . ',' . $lng;
					}
				}
				$url .= '&path=' . rawurlencode($s);
			}
		}

        return $url;
    }

}
