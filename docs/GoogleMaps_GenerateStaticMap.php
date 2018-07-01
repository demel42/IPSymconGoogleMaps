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

// Mittelpunkt der Karte
$map['center'] = $points[0];

$map['zoom'] = 20;
$map['size'] = '640x640';
$map['scale'] = 1;
$map['maptype'] = 'satellite';

$styles = [];
$styles[] = [
        'feature'   => 'road.local',
        'color'     => '0xff00ff',
    ];
$styles[] = [
        'feature'   => 'poi.park',
        'color'     => '0x00ff00',
    ];
$map['styles'] = $styles;

$markers = [];

$marker_points = [];
$marker_points[0] = $points[0];

$markers[] = [
        'color'     => 'green',
        'label'		   => 'P',
        'points'    => $marker_points,
    ];

$marker_points = [];
$marker_points[0] = $points[1];
$marker_points[1] = $points[2];

$markers[] = [
        'color'     => '0x0000ff',
        'size'      => 'tiny',
        'points'    => $marker_points,
    ];

$map['markers'] = $markers;

$paths = [];
$paths[] = [
        'color'     => '0xff0000ff',       // 0xhhhhhhoo oo=opacity
        'weight'    => 2,
        'points'    => $points,
    ];

$map['paths'] = $paths;

$url = GoogleMaps_GenerateStaticMap(1234 /* ID von GoogleMaps-Instanz */, $map);

$html = '<img width="500", height="500" src="' . $url . '" />';
SetValueString(4711 /* ID von HtmlBox-Variable */, $html);
