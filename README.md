# IPSymconGoogleMaps

[![IPS-Version](https://img.shields.io/badge/Symcon_Version-4.4+-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Module-Version](https://img.shields.io/badge/Modul_Version-1.1-blue.svg)
![Code](https://img.shields.io/badge/Code-PHP-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![StyleCI](https://github.styleci.io/repos/138596707/shield?branch=master)](https://github.styleci.io/repos/138596707)

## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Installation](#3-installation)
4. [Funktionsreferenz](#4-funktionsreferenz)
5. [Konfiguration](#5-konfiguration)
6. [Anhang](#6-anhang)
7. [Versions-Historie](#7-versions-historie)

## 1. Funktionsumfang

Erstellen von dynamischen, statischen und eingebetten Karten von GoogleMaps.

a) dynamische Karten
bei diesen Karten sind alle bekannten Mechanismen (zoomen, verschieben) vorhanden sowie auch Routenplanung und Verkehrslage (noch in Arbeit)
Diese API setzt im Aufruf JavaScript voraus; nach meinen bisherigen Tests ist die Einbindung im IPS nur über den Umweg über ein WebHook oder eine HTML-Seite im User-Verzeichnis möglich

b) statisch Karten
diese Art der Karte ist ein Images, in dem Markierungen und Pfade eingezeichnet sind.
Die typische Interaktion (z.B. zoomen, verschieben) ist nicht möglich. Die üblichen Sichten (Karte, Satellit ...) werden unterstützt, nicht aber Routenplanung, Verkehrslage etc.

Der Abruf erfolgt komplett über eine normale (wenn auch komplexe) URL, die Einbindung im IPS kann ganz normal über eine HTML-Box erfolgen.

c) eingebettete Karten
das sind Karten mit Sonderfunktion, zur Zeit wird nur __directions__ (Wegekarte) unterstützt

## 2. Voraussetzungen

 - IP-Symcon ab Version 4.4
 - API von GooleMaps (siehe [hier|(#7-GoogleMaps)]

## 3. Installation

### a. Laden des Moduls

Die Konsole von IP-Symcon öffnen. Im Objektbaum unter Kerninstanzen die Instanz _Modules_ durch einen doppelten Mausklick öffnen.

In der _Modules_ Instanz rechts oben auf den Button _Hinzufügen_ drücken.

In dem sich öffnenden Fenster folgende URL hinzufügen:

`https://github.com/demel42/IPSymconGoogleMaps.git`

und mit _OK_ bestätigen.

Anschließend erscheint ein Eintrag für das Modul in der Liste der Instanz _Modules_

### b. Einrichtung in IPS

In IP-Symcon nun _Instanz hinzufügen_ (_CTRL+1_) auswählen unter der Kategorie, unter der man die Instanz hinzufügen will, und Hersteller _(sonstiges)_ und als Gerät _GoogleMaps_ auswählen.

In dem Konfigurationsdialog den API-Key von GoogleMaps eintragen.

## 4. Funktionsreferenz

Alle Konfigurationen werden als json-encoded-String übergeben. Dabei werden, soweit möglich, die orinigal Bezeichnungen als Element-Name verwendet. In der Javascript-API werden die entsprechnden Strukturen 1:1 weitergegeben… damizt sind die in der Dokumentation beschriebenen Einstellungen möglich.
In den Beispielen sind die Strukturen soweit möglich erklärt, Details sind in den unten angegeben API-Dokumenten zu finden.

### dynamische Karte (Maps JavaScript API)

'GoogleMaps_GenerateDynamicMap(integer $InstanzID, string $jsonData)`

API-Dokumentation: https://developers.google.com/maps/documentation/javascript/tutorial,
https://developers.google.com/maps/documentation/javascript/reference/3/
<br>
Beispiel: `docs/GoogleMaps_GenerateDynamicMap_WebHook.php` und `docs/GoogleMaps_GenerateDynamicMap_HtmlBox.php`

### statische Karte (Maps Static API)

'GoogleMaps_GenerateStaticMap(integer $InstanzID, string $jsonData)`

API-Dokumentation: https://developers.google.com/maps/documentation/maps-static/intro<br>
Beispiel: `docs/GoogleMaps_GenerateStaticMap.php`

### eingebettete Karte (Maps Embed API)

'GoogleMaps_GenerateEmbededMap(integer $InstanzID, string $jsonData)`

API-Dokumentation: https://developers.google.com/maps/documentation/embed/guide<br>
Beispiel: `docs/GoogleMaps_GenerateEmbededMap.php`

## 5. Konfiguration:

### Variablen

| Eigenschaft               | Typ      | Standardwert | Beschreibung |
| :-----------------------: | :-----:  | :----------: | :------------------------------------------------------------------: |
| api_key                   | string   |              | API-Key von GoogleMaps |

Zur Unterstützung der Konfiguration gibt es die Schaltfläche _Prüfe Konfigurætion_, die versucht, eine entsprechenden HTTP-Call zu machen und so den API.Key und die Berechtigungen zu testen. Für __GoogleMaps_GenerateDynamicMap__ gibt es leider keine einfache Möglichkeit.

## 6. Anhang

GUIDs

- Modul: `{B15FD42A-E24E-481E-9472-3D99CFF0EE0B}`
- Instanzen:
  - GoogleMaps: `{2C639155-4F49-4B9C-BBA5-1C7E62F1CF54}`

GoogleMaps

Grundsätzlich ist nach den letzten Änderungen laut Dokumentation von Google ein Zugriff auf Karten von GoogleMaps nur noch mit einen API-Key möglich. Bei den statischen Maps scheint der Zugriff aber noch ohen API-Key zu funktionieren.
Dieser API-Key setzt eine Registrierung bei Google voraus und eine Angabe eine Zahlungsmittels. Es gibt nach meinem Verständnis ein monatliches Guthaben von 200$, das nach der jetzigen Preisliste für 28.500 Aufrufe von dynamischen Karten/Monat bzw. 100.000 statischen Karte bzw. 40.000 Routen ausreicht.
siehe https://cloud.google.com/maps-platform/pricing/?hl=de

Achtung: meine Informationen haben Stand 06/2018, stellen nur meine Meinung dar und natürlich übernehme ich keine Gewähr für die Richtigkeit!

Der API-Key kann hier https://developers.google.com/maps/documentation/javascript/get-api-key?hl=de erstellt werden.

Für die Karten muss man die benötigten API's (siehe unten) aktivieren und ggfs in dem Projekt unter API-Beschränkungen eintragen.

![API-Library](docs/API-Library.png?raw=true "Bibliothek")
![API-Accesscontrol](docs/API-Accesscontrol.png?raw=true "Übersicht")
![API-Limitations](docs/API-Limitations.png?raw=true "Beschränkungen")

## 7. Versions-Historie

- 1.1 @ 06.09.2018 18:48<br>
  Versionshistorie dazu,
  define's der Variablentypen,
  Schaltfläche mit Link zu README.md im Konfigurationsdialog
