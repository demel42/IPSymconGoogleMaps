<?php

require_once __DIR__ . '/../libs/common.php';  // globale Funktionen

if (@constant('IPS_BASE') == null) {
    // --- BASE MESSAGE
    define('IPS_BASE', 10000);							// Base Message
    define('IPS_KERNELSHUTDOWN', IPS_BASE + 1);			// Pre Shutdown Message, Runlevel UNINIT Follows
    define('IPS_KERNELSTARTED', IPS_BASE + 2);			// Post Ready Message
    // --- KERNEL
    define('IPS_KERNELMESSAGE', IPS_BASE + 100);		// Kernel Message
    define('KR_CREATE', IPS_KERNELMESSAGE + 1);			// Kernel is beeing created
    define('KR_INIT', IPS_KERNELMESSAGE + 2);			// Kernel Components are beeing initialised, Modules loaded, Settings read
    define('KR_READY', IPS_KERNELMESSAGE + 3);			// Kernel is ready and running
    define('KR_UNINIT', IPS_KERNELMESSAGE + 4);			// Got Shutdown Message, unloading all stuff
    define('KR_SHUTDOWN', IPS_KERNELMESSAGE + 5);		// Uninit Complete, Destroying Kernel Inteface
    // --- KERNEL LOGMESSAGE
    define('IPS_LOGMESSAGE', IPS_BASE + 200);			// Logmessage Message
    define('KL_MESSAGE', IPS_LOGMESSAGE + 1);			// Normal Message
    define('KL_SUCCESS', IPS_LOGMESSAGE + 2);			// Success Message
    define('KL_NOTIFY', IPS_LOGMESSAGE + 3);			// Notiy about Changes
    define('KL_WARNING', IPS_LOGMESSAGE + 4);			// Warnings
    define('KL_ERROR', IPS_LOGMESSAGE + 5);				// Error Message
    define('KL_DEBUG', IPS_LOGMESSAGE + 6);				// Debug Informations + Script Results
    define('KL_CUSTOM', IPS_LOGMESSAGE + 7);			// User Message
}

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
        $this->SetStatus(102);
    }

    public function VerifyConfiguration()
    {
        $msg = $this->Translate('Status') . ':';

        $api_key = $this->ReadPropertyString('api_key');

        // GenerateStaticMap
        $map['size'] = '500x500';
        $url = $this->GenerateStaticMap(json_encode($map));
        $s = $this->check_url($url);
        $this->SendDebug(__FUNCTION__, 'result=' . $s, 0);
        if ($msg != '') {
            $msg .= "\n";
        }
        $msg .= ' - StaticMap: ' . ($s == '' ? 'ok' : $s);

        // GenerateEmbededMap
        $url = 'https://www.google.com/maps/embed/v1/directions?key=' . $api_key;
        $map['origin'] = 'Rheinallee 1, 53173 Bonn, DE';
        $map['destination'] = 'Barbarossaplatz 1, 50674 Köln, DE';
        $url = $this->GenerateEmbededMap(json_encode($map));
        $s = $this->check_url($url);
        $this->SendDebug(__FUNCTION__, 'result=' . $s, 0);
        if ($msg != '') {
            $msg .= "\n";
        }
        $msg .= ' - EmbededMap: ' . ($s == '' ? 'ok' : $s);

        // GenerateDynamicMap
        $url = 'https://maps.googleapis.com/maps/api/js?key=' . $api_key;
        $s = $this->check_url($url);
        $this->SendDebug(__FUNCTION__, 'result=' . $s, 0);
        if ($msg != '') {
            $msg .= "\n";
        }
        $msg .= ' - DynamicMap: ' . $this->Translate('no simple method to check avail');

        echo $msg;
    }

    private function check_url($url)
    {
        $this->SendDebug(__FUNCTION__, 'http-get: url=' . $url, 0);
        $time_start = microtime(true);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $cdata = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $duration = floor((microtime(true) - $time_start) * 100) / 100;
        $this->SendDebug(__FUNCTION__, ' => httpcode=' . $httpcode . ', duration=' . $duration . 's', 0);

        $statuscode = 0;
        $err = '';
        if ($httpcode != 200) {
            if ($httpcode == 403) {
                $err = "got http-code $httpcode (forbidden)";
                $statuscode = 204;
            } elseif ($httpcode >= 500 && $httpcode <= 599) {
                $statuscode = 202;
                $err = "got http-code $httpcode (server error)";
            } else {
                $err = "got http-code $httpcode";
                $statuscode = 203;
            }
        } else {
            $cdata = '';
        }

        if ($statuscode) {
            $this->SendDebug(__FUNCTION__, ' => statuscode=' . $statuscode . ', err=' . $err, 0);
            $this->SetStatus($statuscode);
        }

        return $cdata;
    }

    private function getMyLocation()
    {
        $id = IPS_GetObjectIDByName('Location', 0);
        if (IPS_GetKernelVersion() >= 5) {
            $loc = IPS_GetProperty($id, 'Location');
            $lat = $loc->latitude;
            $lng = $loc->longitude;
        } else {
            $lat = IPS_GetProperty($id, 'Latitude');
            $lng = IPS_GetProperty($id, 'Longitude');
        }
        $loc = json_encode(['lng' => $lng, 'lat' => $lat]);
        return $loc;
    }

    public function GenerateDynamicMap(string $data)
    {
        $api_key = $this->ReadPropertyString('api_key');
        if ($api_key == '') {
            $this->LogMessage(__FUNCTION__ . ': a valid API-Key is requіred', KL_WARNING);
            return '';
        }

        $url = 'https://maps.googleapis.com/maps/api/js?key=' . $api_key;

        $map = json_decode($data, true);
        $center = isset($map['center']) ? $map['center'] : json_decode($this->getMyLocation(), true);
        $map_options = isset($map['map_options']) ? $map['map_options'] : '';
        $infowindow_options = isset($map['infowindow_options']) ? $map['infowindow_options'] : '';
        $markers = isset($map['markers']) ? $map['markers'] : '';
        $paths = isset($map['paths']) ? $map['paths'] : '';
        $layers = isset($map['layers']) ? $map['layers'] : '';

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

        if ($layers != '') {
            foreach ($layers as $layer) {
                switch ($layer) {
                    case 'traffic':
                        $html .= '
                var trafficLayer = new google.maps.TrafficLayer();
                trafficLayer.setMap(map);
';
                        break;
                    case 'transit':
                        $html .= '
                var transitLayer = new google.maps.TransitLayer();
                transitLayer.setMap(map);
';
                        break;

                    case 'bike':
                        $html .= '
                var bikeLayer = new google.maps.BicyclingLayer();
                bikeLayer.setMap(map);
';
                        break;
                    default:
                        break;
                }
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

    public function GenerateStaticMap(string $data)
    {
        $url = 'https://maps.googleapis.com/maps/api/staticmap?key=';

        $api_key = $this->ReadPropertyString('api_key');
        if ($api_key != '') {
            $url .= $api_key;
        }

        $map = json_decode($data, true);
        $center = isset($map['center']) ? $map['center'] : json_decode($this->getMyLocation(), true);
        $lat = $this->format_float($center['lat']);
        $lng = $this->format_float($center['lng']);
        $url .= '&center=' . rawurlencode($lat . ',' . $lng);

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
                        $lat = $this->format_float($point['lat']);
                        $lng = $this->format_float($point['lng']);
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
                        $lat = $this->format_float($point['lat']);
                        $lng = $this->format_float($point['lng']);
                        if ($s != '') {
                            $s .= '|';
                        }
                        $s .= $lat . ',' . $lng;
                    }
                }
                $url .= '&path=' . rawurlencode($s);
            }
        }

        $n = strlen($url);
        if ($n > 8192) {
            $this->SendDebug(__FUNCTION__, 'size of url=' . $n . ' (max=8192), url=' . $url, 0);
            $this->LogMessage(__FUNCTION__ . ': size of url=' . $n . ', max=8192', KL_WARNING);
            $url = '';
        }

        return $url;
    }

    public function GenerateEmbededMap(string $data)
    {
        $api_key = $this->ReadPropertyString('api_key');
        if ($api_key == '') {
            $this->LogMessage(__FUNCTION__ . ': a valid API-Key is requіred', KL_WARNING);
            return '';
        }

        $map = json_decode($data, true);

        // basic_mode: directions, place, search, view, streetview
        $basic_mode = isset($map['basic_mode']) ? $map['basic_mode'] : 'directions';

        $url = 'https://www.google.com/maps/embed/v1/' . $basic_mode . '?key=' . $api_key;

        if ($basic_mode == 'directions') {
            if (isset($map['origin'])) {
                if (isset($map['origin']['lat']) && isset($map['origin']['lng'])) {
                    $lat = $map['origin']['lat'];
                    $lng = $map['origin']['lng'];
                    $origin = $lat . ',' . $lng;
                    $origin = $this->format_float($lat) . ',' . $this->format_float($lng);
                } else {
                    $origin = $map['origin'];
                }
                $url .= '&origin=' . rawurlencode($origin);
            }

            if (isset($map['destination'])) {
                if (isset($map['destination']['lat']) && isset($map['destination']['lng'])) {
                    $lat = $this->format_float($map['destination']['lat']);
                    $lng = $this->format_float($map['destination']['lng']);
                    $destination = $lat . ',' . $lng;
                } else {
                    $destination = $map['destination'];
                }
                $url .= '&destination=' . rawurlencode($destination);
            }

            // avoid : tolls, ferries, highways
            $avoid = isset($map['avoid']) ? $map['avoid'] : '';
            if ($avoid != '') {
                $s = '';
                foreach ($avoid as $a) {
                    if ($s != '') {
                        $s .= '|';
                    }
                    $s .= $a;
                }
                $url .= '&avoid=' . rawurlencode($s);
            }

            // mode : driving, walking, bicycling, transit, flying
            if (isset($map['mode'])) {
                $url .= '&mode=' . rawurlencode($map['mode']);
            }
        } else {
            $this->LogMessage(__FUNCTION__ . ': unsupported basic-mode "' . $basic_mode . '"', KL_WARNING);
            $url = '';
        }

        return $url;
    }

	private function format_float($number, $dec_points = 6)
	{
		$restult = '';
		if (is_numeric($number)) {
			$nk = abs($number - floor($number));
			$n = strlen(floatval($nk));
			$d = ($n > 1) ? $n - 2 : 0;
			if ($d < $dec_points)
				$dec_points = $d;
			$result = number_format($number, $dec_points, '.', '');
		}
		return $result;
	}
}
