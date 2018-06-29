<?

$url = 'http://<IPS-IP/Name>:3777/hook/GoogleMaps';

$html = '<iframe width="500", height="500" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="' . $url . '"></iframe>';
SetValueString(4711 /* ID von HtmlBox-Variablen */, $html);
