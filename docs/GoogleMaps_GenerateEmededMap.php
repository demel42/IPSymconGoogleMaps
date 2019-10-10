<?php

declare(strict_types=1);

// allgemeine Angaben zur Karte
$map = [];

// Karten-Modus: directions (default), unsupported: place, search, view, streetview
// $map['basic_mode'] = 'directions';

// Startpunkt der Route der Karte
$map['origin'] = 'Rheinallee 1, 53173 Bonn, DE';
$map['destination'] = 'Barbarossaplatz 1, 50674 Köln, DE';

// meiden von (optional): tolls, ferries, highways
$map['avoid'] = ['ferries', 'tolls'];

// Art der Bewegung (optional): driving (default), walking, bicycling, transit, flying
// $map['mode'] = 'driving';

$url = GoogleMaps_GenerateEmbededMap(4711 /*[GoogleMaps]*/, json_encode($map));

$html = '<iframe width="500", height="500" frameborder="0" style="border:0" scrolling="no" marginheight="0" marginwidth="0" src="' . $url . '" />';
SetValueString(1230 /*[GoogleMaps\Karte (embeded)]*/, $html);
