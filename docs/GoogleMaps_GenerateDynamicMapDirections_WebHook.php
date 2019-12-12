<?php

declare(strict_types=1);

$map = [];
// $map['center'] = ['lng' => 11.1018, 'lat' => 47.70875];
$map_options = [
    'zoom'      => 15,
    'tilt'      => 0,
    'mapTypeId' => 'roadmap',
];
$map['map_options'] = $map_options;

$infowindow_options = [
    'maxWidth'  => 200,
];
$map['infowindow_options'] = $infowindow_options;

$map['layers'] = ['traffic'];

$drivingOptions = [
    // 'departureTime' => strtotime('tomorrow 08:00'),
    'trafficModel'     => 'bestguess',           // bestguess, pessemistic, optimistic
];
$transitOptions = [
    'arrivalTime' => strtotime('tomorrow 12:00'),
    // 'departureTime'  => strtotime('tomorrow 08:00'),
    'modes'             => ['bus', 'rail'],      // bus, rail, subway, train, tram
    'routingPreference' => 'fewer_transfers',    // less_walking, fewer_transfers
];
$waypoints = [];

$waypoints[] = [
    'location'          => 'Essen, Burgfeldstraße',
    'stopover'          => true,
];
$waypoints[] = [
    'location'          => ['lng' => 7.006216, 'lat' => 51.452160], // 'Essen, Maxstraße 40',
    // 'stopover'       => true,
];
$map['directions'] = [
    'origin'                   => 'Essen, Zeche Zollverein', // ['lng' => 7.043917, 'lat' => 51.487743],
    'destination'              => 'Mülheim, Rhein-Ruhr-Zentrum',
    'travelMode'               => 'driving',       // driving, walking, bicycling, transit, flying
    'drivingOptions'           => $drivingOptions, // nur, wenn travelMode == driving
    // 'transitOptions'        => $transitOptions, // nur wenn travelMode == transit
    'waypoints'                => $waypoints,
    'provideRouteAlternatives' => true,
    'avoidTolls'               => true,
    'avoidFerries'             => true,
    'avoidHighways'            => true,
];

$html = GoogleMaps_GenerateDynamicMap(1234 /* ID von GoogleMaps-Instanz */, json_encode($map));
echo $html;
