
# URL Shortener

Dies ist eine einfache Webanwendung zum Verkürzen von URLs. Sie ermöglicht es Benutzern, lange URLs in kürzere, einfachere Links zu konvertieren, die leichter zu teilen und zu verwalten sind.

## Ordnerstruktur

```
C:.
└───GitUpload
    │   db.php
    │   index.php
    │   url_shortener.sql
    │
    ├───assets
    │   │   favicon.ico
    │   └───img
    │           bg-masthead.jpg
    │           ipad.png
    │
    ├───css
    │       styles.css
    │
    └───js
            scripts.js
```

### Dateien und Verzeichnisse:

- `db.php`: Verbindung zur Datenbank, um die URL-Informationen zu speichern.
- `index.php`: Hauptdatei, die die Benutzeroberfläche anzeigt und die Verkürzung der URL durchführt.
- `url_shortener.sql`: SQL-Datei für das Erstellen der notwendigen Datenbanktabellen.
- `assets/`: Enthält das Favicon und Bilder für die Website.
  - `favicon.ico`: Symbol für die Webseite.
  - `img/`: Enthält die Bilder, die auf der Webseite verwendet werden.
- `css/`: Stylesheet für die Webseite.
  - `styles.css`: Enthält alle CSS-Stile für die Webseite.
- `js/`: JavaScript-Dateien für die interaktiven Funktionen der Website.
  - `scripts.js`: JavaScript-Datei für die Webseite.

## Voraussetzungen

- Ein Webserver mit PHP-Unterstützung (z.B. Apache).
- Eine MySQL-Datenbank.
- PHP 7.0 oder höher.

## Installation

1. **Datenbank einrichten**:
   - Importiere die `url_shortener.sql`-Datei in deine MySQL-Datenbank, um die notwendigen Tabellen zu erstellen.

2. **Datenbank konfigurieren**:
   - Öffne die `db.php` und passe die Datenbankzugangsdaten an (`host`, `user`, `password`, `dbname`).

3. **Webserver einrichten**:
   - Stelle sicher, dass dein Webserver richtig konfiguriert ist und das Verzeichnis erreichbar ist.
   - Lege die Dateien in dein Webverzeichnis (z.B. `/var/www/html/` auf einem Apache-Server).

4. **Zugriff**:
   - Öffne einen Webbrowser und navigiere zur Startseite der Anwendung (z.B. `http://localhost/` oder die entsprechende URL deines Servers).

## Nutzung

1. Auf der Startseite der Anwendung gibt es ein Eingabefeld, in das du die zu verkürzende URL eingeben kannst.
2. Nach dem Absenden wird eine kürzere URL generiert, die im Browser angezeigt wird.
3. Diese kurze URL kann kopiert und weiterverwendet werden, um die Original-URL aufzurufen.

## Anpassungen

- **Styling**: Die Stile der Anwendung können in der Datei `css/styles.css` angepasst werden.
- **Funktionalität**: Die Logik der Verkürzung sowie das Verhalten der Webseite können in `index.php` und `js/scripts.js` verändert werden.
