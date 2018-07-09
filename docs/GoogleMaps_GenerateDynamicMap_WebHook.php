<?php

$points = [
    ['lat' => 51.56094, 'lng' => 7.1583683333333],
    ['lat' => 51.560923333333, 'lng' => 7.1584033333333],
    ['lat' => 51.560871666667, 'lng' => 7.1584216666667],
    ['lat' => 51.56093, 'lng' => 7.1583233333333],
    ['lat' => 51.560976666667, 'lng' => 7.1583516666667],
    ['lat' => 51.560961666667, 'lng' => 7.1583083333333],
    ['lat' => 51.561028333333, 'lng' => 7.1582316666667],
    ['lat' => 51.561013333333, 'lng' => 7.1582666666667],
    ['lat' => 51.561061666667, 'lng' => 7.1583],
    ['lat' => 51.561058333333, 'lng' => 7.158265],
];

// allgemeine Angaben zur Karte
$map = [];

// Zentrum der Karte
$map['center'] = $points[0];

// Optionen für google.maps.Map
$map_options = [
        'zoom'      => 20,              // 1: World, 5: Landmass/continent, 10: City, 15: Streets, 20: Buildings
        'tilt'      => 0,               // 0..45
        'mapTypeId' => 'satellite',     // roadmap, terrain, hybrid, satellite
    ];
$map['map_options'] = $map_options;

// Standard-Optionen für google.maps.InfoWindow
$infowindow_options = [
        'maxWidth'  => 200,				// pixel
    ];
$map['infowindow_options'] = $infowindow_options;

// Karte mit Positionen
$markers = [];

$marker = [];

$marker_points = [];
$marker_points[0] = $points[0];

// Text eines Punktes für google.maps.InfoWindow
$marker_points[0]['info'] = 'akt. Position';

// Optionen eines Punktes für google.maps.Marker
$marker_options = [
        'icon'		=> [
            'url'        => 'http://maps.google.com/mapfiles/kml/paddle/grn-diamond.png',
            'scaledSize' => [
                    'width'		=> 32,
                    'height'	=> 32
                ]
            ]
    ];
$marker_points[0]['marker_options'] = $marker_options;

// GPS-Punkte
$marker['points'] = $marker_points;

// Standard-Optionen für google.maps.Marker
$marker_options = [
        'icon'		=> 'http://maps.google.com/mapfiles/kml/paddle/blu-blank.png',
    ];
$marker['marker_options'] = $marker_options;

$markers[] = $marker;

$marker = [];

$marker_points = [];
$marker_points[0] = $points[1];

$marker['points'] = $marker_points;

$marker['marker_options'] = $marker_options;

$markers[] = $marker;

$map['markers'] = $markers;

// Karte mit verbundenen Punkten
$paths = [];

$path = [];

// GPS-Punkte
$path['points'] = $points;

// Optionen für google.maps.Polyline
$polyline_options = [
        'strokeColor'    => '#FF0000',
        'strokeOpacity'  => 1.0,		// 0.0 .. 1.0
        'strokeWeight'   => 2,			// pixel
    ];

$path['polyline_options'] = $polyline_options;

$paths[] = $path;

$map['paths'] = $paths;

// $map['layers'] = []; // traffic, transit, bike

$html = GoogleMaps_GenerateDynamicMap(1234 /* ID von GoogleMaps-Instanz */, json_encode($map));
echo $html;
