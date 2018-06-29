# IPSymconGoogleMaps

[![IPS-Version](https://img.shields.io/badge/Symcon_Version-4.4+-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Module-Version](https://img.shields.io/badge/Modul_Version-0.1-blue.svg)
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

## 1. Funktionsumfang

Erstellen von statischen und dynamischen Karten von GoogleMaps.

a) statisch Karten
diese Art der Karte ist ein Images, in dem Markierungen und Pfade eingezeichnet sind.
Die typische Interaktion (z.B. zoomen, verschieben) ist nicht möglich. Die üblichen Sichten (Karte, Satellit ...) werden unterstützt, nicht aber Routenplanung, Verkehrslage etc.

Der Abruf erfolgt komplett über eine normale (wenn auch komplexe) URL, die Einbindung im IPS kann ganz normal über eine HTML-Box erfolgen.

b) dynamische Karten
bei diesen Karten sind alle bekannten Mechanismen (zoomen, verschieben) vorhanden sowie auch Routenplanung und Verkehrslage (noch in Arbeit)
Diese API setzt im Aufruf JavaScript voraus; nach meinen bisherigen Tests ist die Einbindung im IPS nur über den Umweg über ein WebHook oder eine HTML-Seite im User-Verzeichnis möglich


Grundsätzlich ist nach den letzten Änderungen laut Dokumentation von Google ein Zugriff auf Karten von GoogleMaps nur noch mit einen API-Key möglich. Bei den statischen Maps scheint der Zugriff aber noch ohen API-Key zu funktionieren.
Dieser API-Key setzt eine Registrierung bei Google voraus und eine Angabe eine Zahlungsmittels. ∆s gibt nach meinem Verständnis ein monatliches Guthaben von 200$, das nach der jetzigen Preisliste für 28.500 Aufrufe von dynamischen Karten/Monat bzw. 100.000 statischen Karte bzw. 40.000 Routen ausreicht.
siehe https://cloud.google.com/maps-platform/pricing/?hl=de

Achtung: meine Informationen haben Stand 06/2018, stellen nur meine Meinung dar und natürlich übernehme ich keine Gewähr für die Richtigkeit!

Der API-Key kann hier https://developers.google.com/maps/documentation/javascript/get-api-key?hl=de erstellt werden.

Für die Karten muss man zwei API's aktivieren: _*Maps Javascript API*_ und _*Maps Static API*_

![API-Übersicht](docs/API-Übersicht.png?raw=true "Übersicht")
![API-Beschränkungen](docs/API-Beschränkungen.png?raw=true "Beschränkungen")

## 2. Voraussetzungen

 - IP-Symcon ab Version 4.4
 - API von GooleMaps

## 3. Installation

### a. Laden des Moduls

Die Konsole von IP-Symcon öffnen. Im Objektbaum unter Kerninstanzen die Instanz __*Modules*__ durch einen doppelten Mausklick öffnen.

In der _Modules_ Instanz rechts oben auf den Button __*Hinzufügen*__ drücken.

In dem sich öffnenden Fenster folgende URL hinzufügen:

`https://github.com/demel42/IPSymconGoogleMaps.git`

und mit _OK_ bestätigen.

Anschließend erscheint ein Eintrag für das Modul in der Liste der Instanz _Modules_

### b. Einrichtung in IPS

In IP-Symcon nun _Instanz hinzufügen_ (_CTRL+1_) auswählen unter der Kategorie, unter der man die Instanz hinzufügen will, und Hersteller _(sonstiges)_ und als Gerät _GoogleMaps_ auswählen.

In dem Konfigurationsdialog den API-Key von GoogleMaps eintragen.

## 4. Funktionsreferenz

### statische Karte

'GoogleMaps_GenerateDynamicMap(integer $InstanzID, $options, $markers, $paths)`

API-Dokumentation: https://developers.google.com/maps/documentation/maps-static/intro?hl=de<br>
Beispiel: `docs/GoogleMaps_GenerateStaticMap.php`

### dynamische Karte

'GoogleMaps_GenerateStaticMap(integer $InstanzID, $options, $markers, $paths)`

API-Dokumentation: https://developers.google.com/maps/documentation/javascript/tutorial?hl=de<br>
Beispiel: `docs/GoogleMaps_GenerateDynamicMap_WebHook.php` und `docs/GoogleMaps_GenerateDynamicMap_HtmlBox.php`

## 5. Konfiguration:

### Variablen

| Eigenschaft               | Typ      | Standardwert | Beschreibung |
| :-----------------------: | :-----:  | :----------: | :------------------------------------------------------------------: |
| api_key                   | string   |              | API-Key von GoogleMaps |

## 6. Anhang

GUIDs

- Modul: `{B15FD42A-E24E-481E-9472-3D99CFF0EE0B}`
- Instanzen:
  - GoogleMaps: `{2C639155-4F49-4B9C-BBA5-1C7E62F1CF54}`
