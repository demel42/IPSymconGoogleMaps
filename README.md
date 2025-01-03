# IPSymconGoogleMaps

[![IPS-Version](https://img.shields.io/badge/Symcon_Version-6.0+-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Code](https://img.shields.io/badge/Code-PHP-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)

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

 - IP-Symcon ab Version 6.0<br>
   Version 4.4 mit Branch _ips_4.4_ (nur noch Fehlerkorrekturen)
 - API von GooleMaps (siehe [Anhang/GoogleMaps](#6-anhang))

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

`GoogleMaps_GenerateDynamicMap(integer $InstanzID, string $jsonData)`

API-Dokumentation: https://developers.google.com/maps/documentation/javascript/tutorial,
https://developers.google.com/maps/documentation/javascript/reference/3/
<br>
Beispiel: 
[docs/GoogleMaps_GenerateDynamicMap_HtmlBox.php](docs/GoogleMaps_GenerateDynamicMap_HtmlBox.php)
sowie 
[docs/GoogleMaps_GenerateDynamicMap_WebHook.php](docs/GoogleMaps_GenerateDynamicMap_WebHook.php)
bzw
[docs/GoogleMaps_GenerateDynamicMapDirections_WebHook.php](docs/GoogleMaps_GenerateDynamicMapDirections_WebHook.php)

### statische Karte (Maps Static API)

`GoogleMaps_GenerateStaticMap(integer $InstanzID, string $jsonData)`

API-Dokumentation: https://developers.google.com/maps/documentation/maps-static/intro<br>
Beispiel: [docs/GoogleMaps_GenerateStaticMap.php](docs/GoogleMaps_GenerateStaticMap.php)

Zusatz-Angaben:
- da die Größe der URL auf 8Kb limitiert ist und dieser Wert bei einer größeren Anzahl von Punkte schnell erreicht werde kann, gibt es Zusatz-Optionen, die in _$jsonData_ mit übergeben werden können
  - `restrict_points`<br>
    es werden nur soviel Punkte verwendet, das das Limit nicht überschritten wird
  - `skip_points`<br>
    eine Angabe > 1 bedeutet, das nur jeder x'te Punkt verwendet wird - also 3 bedeutet nur jeder 3. Punkt -. Da Googlemaps ja interpoliert, kann das Ergebnis durchaus zufredenstellend sein
  Falls das nicht Ergebnis nicht zufriedenstellen ist, ist die DynamicMap zu emnpfehlen, die ist hier nicht limitiert.

### eingebettete Karte (Maps Embed API)

`GoogleMaps_GenerateEmbededMap(integer $InstanzID, string $jsonData)`

API-Dokumentation: https://developers.google.com/maps/documentation/embed/guide<br>
Beispiel: [docs/GoogleMaps_GenerateEmbededMap.php](docs/GoogleMaps_GenerateEmbededMap.php)

### Entfernung und Reisedauer-Berechnung (DistanceMatrix API)

API-Dokumentation: https://developers.google.com/maps/documentation/distance-matrix/intro<br>
Beispiel: [docs/GoogleMaps_GetDistanceMatrix.php](docs/GoogleMaps_GetDistanceMatrix.php)

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

Verweise:
- https://console.cloud.google.com/home/dashboard, https://console.cloud.google.com/apis/dashboard
- https://developers.google.com/maps/documentation

GoogleMaps

Grundsätzlich ist nach den letzten Änderungen laut Dokumentation von Google ein Zugriff auf Karten von GoogleMaps nur noch mit einen API-Key möglich.
Bei den statischen Maps scheint der Zugriff aber noch ohen API-Key zu funktionieren.
Dieser API-Key setzt eine Registrierung bei Google voraus und eine Angabe eine Zahlungsmittels. Es gibt nach meinem Verständnis ein monatliches Guthaben von 200$, das nach der jetzigen Preisliste für 28.500 Aufrufe von dynamischen Karten/Monat bzw. 100.000 statischen Karte bzw. 40.000 Routen ausreicht.
siehe https://cloud.google.com/maps-platform/pricing/?hl=de

Achtung: meine Informationen haben Stand 06/2018, stellen nur meine Meinung dar und natürlich übernehme ich keine Gewähr für die Richtigkeit!

Der API-Key kann hier https://developers.google.com/maps/documentation/javascript/get-api-key?hl=de erstellt werden.

Für die Karten muss man die benötigten API's (siehe unten) aktivieren und ggfs in dem Projekt unter API-Beschränkungen eintragen.

![API-Library](docs/API-Library.png?raw=true "Bibliothek")
![API-Accesscontrol](docs/API-Accesscontrol.png?raw=true "Übersicht")
![API-Limitations](docs/API-Limitations.png?raw=true "Beschränkungen")

## 7. Versions-Historie

- 2.9 @ 02.01.2025 14:28
  - interne Änderung
  - update submodule CommonStubs

- 2.8 @ 06.02.2024 09:46
  - Verbesserung: Angleichung interner Bibliotheken anlässlich IPS 7
  - update submodule CommonStubs

- 2.7 @ 03.11.2023
  - Neu: Ermittlung von Speicherbedarf und Laufzeit (aktuell und für 31 Tage) und Anzeige im Panel "Information"
  - update submodule CommonStubs

- 2.6 @ 04.07.2023 14:44
  - Vorbereitung auf IPS 7 / PHP 8.2
  - update submodule CommonStubs
    - Absicherung bei Zugriff auf Objekte und Inhalte

- 2.5 @ 19.12.2022 12:09
  - update submodule CommonStubs

- 2.4.2 @ 19.10.2022 09:16
  - Fix: README
  - update submodule CommonStubs

- 2.4.1 @ 07.10.2022 13:59
  - update submodule CommonStubs
    Fix: Update-Prüfung wieder funktionsfähig

- 2.4 @ 07.09.2022 15:48
  - Fix: Statuscodes falsch nummeriert
  - update submodule CommonStubs

- 2.3 @ 06.07.2022 17:21
  - interne Funktionen sind nun private und ggfs nur noch via IPS_RequestAction() erreichbar
  - Fix: Angabe der Kompatibilität auf 6.2 korrigiert
  - Verbesserung: IPS-Status wird nur noch gesetzt, wenn er sich ändert
  - update submodule CommonStubs

- 2.2.3 @ 17.05.2022 15:38
  - update submodule CommonStubs
    Fix: Absicherung gegen fehlende Objekte

- 2.2.2 @ 10.05.2022 15:06
  - update submodule CommonStubs

- 2.2.1 @ 29.04.2022 18:22
  - Überlagerung von Translate und Aufteilung von locale.json in 3 translation.json (Modul, libs und CommonStubs)

- 2.2 @ 26.04.2022 15:52
  - Implememtierung einer Update-Logik
  - IPS-Version ist nun minimal 6.0
  - Übersetzung vervollständigt
  - diverse interne Änderungen

- 2.1 @ 16.04.2022 11:54
  - Namenskonflikt (trait CommonStubs)
  - Aktualisierung von submodule CommonStubs

- 2.0 @ 13.04.2022 10:47
  - GoogleMaps_GenerateStaticMap() ergänzt um 'restrict_points' und 'skip_points'
  - Anzeige der Referenzen der Instanz incl. Statusvariablen und Instanz-Timer
  - common.php -> libs/CommonStubs

- 1.18 @ 22.12.2020 16:21
  - PHP_CS_FIXER_IGNORE_ENV=1 in github/workflows/style.yml eingefügt
  - GetDistanceMatrix(): Fix wegen strict_types=1

- 1.17 @ 23.07.2020 15:34
  - LICENSE.md hinzugefügt
  - intere Funktionen sind nun "private"
  - define's durch statische Klassen-Variablen ersetzt
  - library.php in local.php umbenannt
  - lokale Funktionen aus common.php in locale.php verlagert

- 1.16 @ 08.04.2020 11:01
  - define's durch statische Klassen-Variablen ersetzt

- 1.15 @ 30.12.2019 10:56
  - Anpassungen an IPS 5.3
    - Formular-Elemente: 'label' in 'caption' geändert
  - Fix in CreateVarProfile()

- 1.14 @ 12.12.2019 11:45
  - GenerateDynamicMap() kann nun auch _DirectionServices_ (siehe `docs/GoogleMaps_GenerateDynamicMapDirections_WebHook.php`)

- 1.13 @ 10.12.2019 09:10
  - mit 'Prüfe Konfiguration' wird ggfs. der Modulstatus ('Zugriff verboten' etc) korrigiert

- 1.12 @ 26.10.2019 04:47
  - Fix wegen strict_types=1

- 1.11 @ 21.10.2019 10:21
  - Fix in GenerateDynamicMap(): Berücksichtigen der 'marker_options' pro 'marker_poit'
  - Anpassungen an IPS 5.2
    - IPS_SetVariableProfileValues(), IPS_SetVariableProfileDigits() nur bei INTEGER, FLOAT
    - Dokumentation-URL in module.json
  - Umstellung auf strict_types=1
  - Umstellung von StyleCI auf php-cs-fixer

- 1.10 @ 09.08.2019 14:32
  - Schreibfehler korrigiert

- 1.9 @ 25.04.2019 15:52
  - Schreibfehler korrigiert (Goole -> Google)

- 1.8 @ 05.04.2019 11:55
  - Fix zu 1.6

- 1.7 @ 29.03.2019 16:19
  - SetValue() abgesichert

- 1.6 @ 21.03.2019 17:04
  - Anpassungen IPS 5, Abspaltung von Branch _ips_4.4_
  - Korrektur: GetDistanceMatrix() liefert als einzige Funktion die Ergebnisse, nicht die URL zurück, daher klappte der Test-Button nicht mehr

- 1.5 @ 23.01.2019 18:18
  - curl_errno() abfragen

- 1.4 @ 04.11.2018 17:50
  - offizielle defines der Status-Codes verwendet sowie eigenen Status-Codes relativ zu _IS_EBASE_ angelegt

- 1.3 @ 28.10.2018 12:07
  - API _DistanceMatrix_ hinzugefügt
  - Korrektur von GenerateStaticMap() und GenerateEmbededMap(): Fehler bei der Formatierung von Longitude und Latitude

- 1.2 @ 08.10.2018 22:32
  - Korrektur des Zugriffs auf _Location_

- 1.1 @ 06.09.2018 18:48
  - Versionshistorie dazu
  - define's der Variablentypen
  - Schaltfläche mit Link zu README.md im Konfigurationsdialog
