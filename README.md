# fw_update_whiteliste
Ein Skript um die Knoten in Wellen zu aktualisieren


Der aktuelle Stand ist ein PoC und wirft einfach in Textform die IPv6 Adressen der Nodes in der jeweiligen Welle raus.

### Aufruf:

```php

php whitelist.php

```

Die URL, welche bestimmt, welche Domäne genutzt wird ist in der whitelist.php


### Offene ToDo's:

- Domäne auswählbar machen
- Server Proxy bauen, welcher anhand der URL und IP erkennt, in welcher Welle ein Node ist.
- Konfigurierbar, welche Welle aktuell für welche Domäne ausgerollt wird.
- Eventuell auswertung, welche Nodes es erfolgreich geschafft haben / Oder von der Karte verschwunen sind.
- Eventuell ein kleines Webinterface zum steuern.


