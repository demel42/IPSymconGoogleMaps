<?php

// allgemeine Angaben zur Karte
$map = [];

// Startpunkt der Route der Karte
$map['origin'] = 'Rheinallee 1, 53173 Bonn, DE';
// $map['origin'] = [ 'lat' => 50.685676, 'lng' => 7.157836 ];
$map['destination'] = 'Barbarossaplatz 1, 50674 Köln, DE';

// meiden von (optional): tolls, ferries, highways
$map['avoid'] = ['ferries', 'tolls'];

// geplante Ankunft (optional) - Auswirkung bei der Berechnung der Reisedauer
$map['arrival_time'] = strtotime('tomorrow 08:00');

// geplante Abreise (optional) - Auswirkung bei der Berechnung der Reisedauer
// $map['departure_time'] = trtotime('tomorrow 06:00');

// Berechnung der Reisedauer (optional): best_guess (default), pessimistic, optimistic
// $map['traffic_model'] = 'best_guess';

// Art der Fortbewegung (optional): driving (default), walking, bicycling, transit, flying
// $map['mode'] = 'driving';

// Verkehrsmittel (nur wenn 'mode' == 'transit'): bus, subway, train, tram, rail
// $map['transit_mode'] = [ 'bus', 'subway' ];

// Präferenz des Transfers (nur wenn 'mode' == 'transit'): less_walking, fewer_transfers
// $map['transit_routing_preference'] = 'fewer_transfers';

$r = GoogleMaps_GetDistanceMatrix(4711 /*[GoogleMaps]*/, json_encode($map));

echo $r . PHP_EOL;
