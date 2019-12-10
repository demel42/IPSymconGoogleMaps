<?php

declare(strict_types=1);

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

        $this->SetStatus($api_key == '' ? IS_INACTIVE : IS_ACTIVE);
    }

    protected function GetFormElements()
    {
        $formElements = [];
        $formElements[] = ['type' => 'ValidationTextBox', 'name' => 'api_key', 'caption' => 'API-Key'];

        return $formElements;
    }

    protected function GetFormActions()
    {
        $formActions = [];
        $formActions[] = ['type' => 'Button', 'label' => 'Verify Configuration', 'onClick' => 'GoogleMaps_VerifyConfiguration($id);'];
        if (IPS_GetKernelVersion() < 5.2) {
            $formActions[] = ['type' => 'Label', 'label' => '____________________________________________________________________________________________________'];
            $formActions[] = [
                'type'    => 'Button',
                'caption' => 'Module description',
                'onClick' => 'echo "https://github.com/demel42/IPSymconGoogleMaps/blob/master/README.md";'
            ];
        }

        return $formActions;
    }

    public function GetConfigurationForm()
    {
        $formElements = $this->GetFormElements();
        $formActions = $this->GetFormActions();
        $formStatus = $this->GetFormStatus();

        $form = json_encode(['elements' => $formElements, 'actions' => $formActions, 'status' => $formStatus]);
        if ($form == '') {
            $this->SendDebug(__FUNCTION__, 'json_error=' . json_last_error_msg(), 0);
            $this->SendDebug(__FUNCTION__, '=> formElements=' . print_r($formElements, true), 0);
            $this->SendDebug(__FUNCTION__, '=> formActions=' . print_r($formActions, true), 0);
            $this->SendDebug(__FUNCTION__, '=> formStatus=' . print_r($formStatus, true), 0);
        }
        return $form;
    }

    public function VerifyConfiguration()
    {
        $this->SendDebug(__FUNCTION__, 'initial: status=' . $this->GetStatusText(), 0);
        if ($this->GetStatus() > IS_INVALIDCONFIG) {
            $this->SetStatus(IS_ACTIVE);
            $this->SendDebug(__FUNCTION__, 'corrected: status=' . $this->GetStatusText(), 0);
        }

        $msg = $this->Translate('Status') . ':';

        $api_key = $this->ReadPropertyString('api_key');

        // GenerateStaticMap
        $map['size'] = '500x500';
        $url = $this->GenerateStaticMap(json_encode($map));
        $r = $this->do_HttpRequest($url, $s);
        $this->SendDebug(__FUNCTION__, 'GenerateStaticMap(): result=' . $s, 0);
        $this->SendDebug(__FUNCTION__, 'GenerateStaticMap(): status=' . $this->GetStatusText(), 0);
        if ($msg != '') {
            $msg .= "\n";
        }
        $msg .= ' - StaticMap: ' . ($r ? 'ok' : $s);

        // GenerateEmbededMap
        $url = 'https://www.google.com/maps/embed/v1/directions?key=' . $api_key;
        $map['origin'] = 'Rheinallee 1, 53173 Bonn, DE';
        $map['destination'] = 'Barbarossaplatz 1, 50674 Köln, DE';
        $url = $this->GenerateEmbededMap(json_encode($map));
        $r = $this->do_HttpRequest($url, $s);
        $this->SendDebug(__FUNCTION__, 'GenerateEmbededMap(): result=' . $s, 0);
        $this->SendDebug(__FUNCTION__, 'GenerateEmbededMap(): status=' . $this->GetStatusText(), 0);
        if ($msg != '') {
            $msg .= "\n";
        }
        $msg .= ' - EmbededMap: ' . ($r ? 'ok' : $s);

        // GenerateDynamicMap
        $url = 'https://maps.googleapis.com/maps/api/js?key=' . $api_key;
        $r = $this->do_HttpRequest($url, $s);
        $this->SendDebug(__FUNCTION__, 'GenerateDynamicMap(): result=' . $s, 0);
        $this->SendDebug(__FUNCTION__, 'GenerateDynamicMap(): status=' . $this->GetStatusText(), 0);
        if ($msg != '') {
            $msg .= "\n";
        }
        $msg .= ' - DynamicMap: ' . $this->Translate('no simple method to check avail');

        // GetDistanceMatrix
        $url = 'https://www.google.com/maps/embed/v1/directions?key=' . $api_key;
        $map['origin'] = 'Rheinallee 1, 53173 Bonn, DE';
        $map['destination'] = 'Barbarossaplatz 1, 50674 Köln, DE';
        $s = $this->GetDistanceMatrix(json_encode($map));
        $this->SendDebug(__FUNCTION__, 'GetDistanceMatrix(): result=' . $s, 0);
        $this->SendDebug(__FUNCTION__, 'GetDistanceMatrix(): status=' . $this->GetStatusText(), 0);
        if ($msg != '') {
            $msg .= "\n";
        }
        $msg .= ' - DistanceMatrix: ' . ($s != '' ? 'ok' : 'fail');

        $this->SendDebug(__FUNCTION__, 'final: status = ' . $this->GetStatusText(), 0);
        echo $msg;
    }

    private function do_HttpRequest($url, &$result)
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
        $cerrno = curl_errno($ch);
        $cerror = $cerrno ? curl_error($ch) : '';
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $duration = round(microtime(true) - $time_start, 2);
        $this->SendDebug(__FUNCTION__, ' => errno=' . $cerrno . ', httpcode=' . $httpcode . ', duration=' . $duration . 's', 0);

        $result = $cdata;
        $statuscode = 0;
        $err = '';
        if ($cerrno) {
            $statuscode = IS_SERVERERROR;
            $err = 'got curl-errno ' . $cerrno . ' (' . $cerror . ')';
        } elseif ($httpcode != 200) {
            if ($httpcode == 403) {
                $err = 'got http-code ' . $httpcode . ' (forbidden)';
                $statuscode = IS_FORBIDDEN;
            } elseif ($httpcode >= 500 && $httpcode <= 599) {
                $statuscode = IS_SERVERERROR;
                $err = 'got http-code ' . $httpcode . ' (server error)';
            } else {
                $err = 'got http-code ' . $httpcode;
                $statuscode = IS_HTTPERROR;
            }
        }

        if ($statuscode) {
            $this->SendDebug(__FUNCTION__, ' => statuscode=' . $statuscode . ', err=' . $err, 0);
            $this->SetStatus($statuscode);
            return false;
        }

        return true;
    }

    private function getMyLocation()
    {
        $id = IPS_GetInstanceListByModuleID('{45E97A63-F870-408A-B259-2933F7EABF74}')[0];
        $loc = json_decode(IPS_GetProperty($id, 'Location'));
        $lat = $loc->latitude;
        $lng = $loc->longitude;
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
        $directions = isset($map['directions']) ? $map['directions'] : '';
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
';
        $lng = (float) $this->format_float((float) $center['lng'], 6);
        $lat = (float) $this->format_float((float) $center['lat'], 6);
        $loc = ['lng' => $lng, 'lat' => $lat];
        $html .= '
                var center = new google.maps.LatLng(' . json_encode($loc) . ');
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
                $marker_points = isset($marker['points']) ? $marker['points'] : [];
                if ($marker_points == []) {
                    continue;
                }
                $locs = [];
                foreach ($marker_points as $marker_point) {
                    $lng = (float) $this->format_float((float) $marker_point['lng'], 6);
                    $lat = (float) $this->format_float((float) $marker_point['lat'], 6);
                    $loc = ['lng' => $lng, 'lat' => $lat];
                    if (isset($marker_point['marker_options'])) {
                        $loc['marker_options'] = $marker_point['marker_options'];
                    }
                    $locs[] = $loc;
                }
                $marker_options = isset($marker['marker_options']) ? $marker['marker_options'] : '';
                $html .= '
                var markerLocations = ' . json_encode($locs) . ';
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
                $path_points = isset($path['points']) ? $path['points'] : [];
                if ($path_points == []) {
                    continue;
                }
                $locs = [];
                foreach ($path_points as $path_point) {
                    $lng = (float) $this->format_float((float) $path_point['lng'], 6);
                    $lat = (float) $this->format_float((float) $path_point['lat'], 6);
                    $locs[] = ['lng' => $lng, 'lat' => $lat];
                }
                $polyline_options = isset($path['polyline_options']) ? $path['polyline_options'] : '';
                $html .= '
                var polylineOptions = ' . json_encode($polyline_options) . ';
                if (polylineOptions == "")
                    polylineOptions = {};
                polylineOptions.path = ' . json_encode($locs) . ';
                var polyline = new google.maps.Polyline(polylineOptions);
                polyline.setMap(map);
';
            }
        }

        // Karte mit Routen
        if ($directions != '') {
            $request = [];
            if (isset($directions['origin'])) {
                if (isset($directions['origin']['lat']) && isset($directions['origin']['lng'])) {
                    $lat = $this->format_float((float) $directions['origin']['lat'], 6);
                    $lng = $this->format_float((float) $directions['origin']['lng'], 6);
                    $origin = $lat . ',' . $lng;
                } else {
                    $origin = $directions['origin'];
                }
                $request['origin'] = $origin;
            }
            if (isset($directions['destination'])) {
                if (isset($directions['destination']['lat']) && isset($directions['destination']['lng'])) {
                    $lat = $this->format_float((float) $directions['destination']['lat'], 6);
                    $lng = $this->format_float((float) $directions['destination']['lng'], 6);
                    $destination = $lat . ',' . $lng;
                } else {
                    $destination = $directions['destination'];
                }
                $request['destination'] = $destination;
            }

            $travelMode = strtoupper($this->GetArrayElem($directions, 'travelMode', 'DRIVING'));
            if (!in_array($travelMode, ['DRIVING', 'BICYCLING', 'TRANSIT', 'WALKING'])) {
                $this->LogMessage(__FUNCTION__ . ': unknown travelMode "' . $travelMode . '"', KL_WARNING);
                $travelMode = 'DRIVING';
            }
            $request['travelMode'] = $travelMode;

            $request['provideRouteAlternatives'] = (bool) $this->GetArrayElem($directions, 'provideRouteAlternatives', false);

            $request['avoidTolls'] = (bool) $this->GetArrayElem($directions, 'avoidTolls', false);
            $request['avoidFerries'] = (bool) $this->GetArrayElem($directions, 'avoidFerries', false);
            $request['avoidHighways'] = (bool) $this->GetArrayElem($directions, 'avoidHighways', false);

            if (isset($directions['drivingOptions'])) {
                $o = $directions['drivingOptions'];
                if (isset($o['departureTime'])) {
                    if (is_numeric($o['departureTime'])) {
                        $t = $o['departureTime'];
                        $o['departureTime'] = date('r', $t);
                    }
                } else {
                    $o['departureTime'] = date('r');
                }
                $request['drivingOptions'] = $o;
            }
            if (isset($directions['transitOptions'])) {
                $o = $directions['transitOptions'];
                if (isset($o['arrivalTime'])) {
                    if (is_numeric($o['arrivalTime'])) {
                        $t = $o['arrivalTime'];
                        $o['arrivalTime'] = date('r', $t);
                    }
                } elseif (isset($o['departureTime'])) {
                    if (is_numeric($o['departureTime'])) {
                        $t = $o['departureTime'];
                        $o['departureTime'] = date('r', $t);
                    }
                } else {
                    $o['departureTime'] = date('r');
                }
                if (isset($o['modes'])) {
                    $md = [];
                    foreach ($o['modes'] as $m) {
                        $md[] = strtoupper($m);
                    }
                    $o['modes'] = $md;
                }
                if (isset($o['routingPreference'])) {
                    $o['routingPreference'] = strtoupper($o['routingPreference']);
                }
                $request['transitOptions'] = $o;
            }

            $html .= '
			var directionsService = new google.maps.DirectionsService();
			var directionsRenderer = new google.maps.DirectionsRenderer();
			directionsRenderer.setMap(map);
			var request = ' . json_encode($request) . ';';
            if (isset($request['drivingOptions']['departureTime'])) {
                $html .= '
			request["drivingOptions"]["departureTime"] = new Date("' . $request['drivingOptions']['departureTime'] . '");';
            }
            if (isset($request['transitOptions']['arrivalTime'])) {
                $html .= '
			request["transitOptions"]["arrivalTime"] = new Date("' . $request['transitOptions']['arrivalTime'] . '");';
            }
            if (isset($request['transitOptions']['departureTime'])) {
                $html .= '
			request["transitOptions"]["departureTime"] = new Date("' . $request['transitOptions']['departureTime'] . '");';
            }
            $html .= '
			directionsService.route(request, function(result, status) {
				if (status == "OK") {
					directionsRenderer.setDirections(result);
				}
			});
';
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
        $lat = $this->format_float((float) $center['lat'], 6);
        $lng = $this->format_float((float) $center['lng'], 6);
        $url .= '&center=' . rawurlencode($lat . ',' . $lng);

        foreach (['zoom', 'size', 'scale', 'maptype'] as $key) {
            if (isset($map[$key])) {
                $url .= '&' . $key . '=' . rawurlencode((string) $map[$key]);
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
                        $lat = $this->format_float((float) $point['lat'], 6);
                        $lng = $this->format_float((float) $point['lng'], 6);
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
                        $lat = $this->format_float((float) $point['lat'], 6);
                        $lng = $this->format_float((float) $point['lng'], 6);
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
                    $lat = $this->format_float((float) $map['origin']['lat'], 6);
                    $lng = $this->format_float((float) $map['origin']['lng'], 6);
                    $origin = $lat . ',' . $lng;
                } else {
                    $origin = $map['origin'];
                }
                $url .= '&origin=' . rawurlencode($origin);
            }

            if (isset($map['destination'])) {
                if (isset($map['destination']['lat']) && isset($map['destination']['lng'])) {
                    $lat = $this->format_float((float) $map['destination']['lat'], 6);
                    $lng = $this->format_float((float) $map['destination']['lng'], 6);
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

    public function GetDistanceMatrix(string $data)
    {
        $api_key = $this->ReadPropertyString('api_key');
        if ($api_key == '') {
            $this->LogMessage(__FUNCTION__ . ': a valid API-Key is requіred', KL_WARNING);
            return '';
        }

        $map = json_decode($data, true);

        $url = 'https://maps.googleapis.com/maps/api/distancematrix/json?key=' . $api_key;

        // language: en, de, ...
        $url .= '&language=de';

        // units: imperial, metric
        $url .= '&units=metric';

        if (isset($map['origin'])) {
            if (isset($map['origin']['lat']) && isset($map['origin']['lng'])) {
                $lat = $this->format_float((float) $map['origin']['lat'], 6);
                $lng = $this->format_float((float) $map['origin']['lng'], 6);
                $origin = $lat . ',' . $lng;
            } else {
                $origin = $map['origin'];
            }
            $url .= '&origins=' . rawurlencode($origin);
        }

        if (isset($map['destination'])) {
            if (isset($map['destination']['lat']) && isset($map['destination']['lng'])) {
                $lat = $this->format_float((float) $map['destination']['lat'], 6);
                $lng = $this->format_float((float) $map['destination']['lng'], 6);
                $destination = $lat . ',' . $lng;
            } else {
                $destination = $map['destination'];
            }
            $url .= '&destinations=' . rawurlencode($destination);
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

        // arrival_time: unix-timestamp UTC
        if (isset($map['arrival_time'])) {
            $url .= '&arrival_time=' . rawurlencode($map['arrival_time']);
        }

        // departure_time: unix-timestamp UTC
        if (isset($map['departure_time'])) {
            $url .= '&departure_time=' . rawurlencode($map['departure_time']);
        }

        // traffic_model: best_guess, pessimistic, optimistic
        if (isset($map['traffic_model'])) {
            $url .= '&traffic_model=' . rawurlencode($map['traffic_model']);
        }

        // transit_mode: bus, subway, train, tram, rail
        $transit_mode = isset($map['transit_mode']) ? $map['transit_mode'] : '';
        if ($transit_mode != '') {
            $s = '';
            foreach ($transit_mode as $a) {
                if ($s != '') {
                    $s .= '|';
                }
                $s .= $a;
            }
            $url .= '&transit_mode=' . rawurlencode($s);
        }

        // transit_routing_preference: less_walking, fewer_transfers
        $transit_routing_preference = isset($map['transit_routing_preference']) ? $map['transit_routing_preference'] : '';
        if ($transit_routing_preference != '') {
            $s = '';
            foreach ($transit_routing_preference as $a) {
                if ($s != '') {
                    $s .= '|';
                }
                $s .= $a;
            }
            $url .= '&transit_routing_preference=' . rawurlencode($s);
        }

        $ok = $this->do_HttpRequest($url, $s);
        return $s;
    }
}
