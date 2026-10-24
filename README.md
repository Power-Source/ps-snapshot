# PS Snapshot

**Deutsch** | [English](README.en.md)

[![Version](https://img.shields.io/badge/Version-1.0.4-2271b1?style=flat-square)](readme.txt)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777bb4?style=flat-square&logo=php&logoColor=white)
![WordPress](https://img.shields.io/badge/WordPress-bis%207.1.0-21759b?style=flat-square&logo=wordpress&logoColor=white)
![ClassicPress](https://img.shields.io/badge/ClassicPress-2.7.4-03768e?style=flat-square)
[![Lizenz](https://img.shields.io/badge/Lizenz-GPL--2.0--or--later-2ea44f?style=flat-square)](https://www.gnu.org/licenses/gpl-2.0.html)


**Zuverlässige Backups und Wiederherstellungen für WordPress und ClassicPress.**

PS Snapshot erstellt Datenbank- und Dateisicherungen direkt aus dem Admin-Bereich. Backups können lokal oder auf externen Zielen gespeichert, geplant, importiert und bei Bedarf wiederhergestellt werden. Das Plugin unterstützt außerdem Multisite-Installationen und bietet ein separates Recovery-Skript für Notfälle.

![PS Snapshot Admin-Oberfläche](screenshot.png)

## Funktionen

- Erstellt Backups von Datenbanktabellen, Dateien oder beidem.
- Speichert Archive lokal, per FTP/SFTP, in Dropbox oder Google Drive.
- Unterstützt einmalige und geplante Backups.
- Importiert vorhandene Backup-Archive.
- Stellt einzelne Snapshots über den Admin-Bereich wieder her.
- Unterstützt WordPress- und ClassicPress-Multisite-Netzwerke.
- Bietet ein eigenständiges Recovery-Skript, wenn der Admin-Bereich nicht mehr erreichbar ist.
- Stellt englische Übersetzungen über die Textdomain `snapshot` bereit.

## Voraussetzungen

| Komponente | Voraussetzung |
| --- | --- |
| PHP | 7.4 oder höher |
| Datenbank | MySQLi-Erweiterung |
| Archive | PHP-Zip-Erweiterung |
| PHP-Laufzeit | Mindestens 150 Sekunden empfohlen |
| CMS | WordPress 4.9+ oder ClassicPress 2.0+ |

> [!IMPORTANT]
> Backups werden nicht verschlüsselt. Schütze Backup-Archive und externe Speicherziele entsprechend, insbesondere wenn sie sensible Daten enthalten.

## Installation

1. Lade das Plugin herunter und entpacke es.
2. Kopiere den Ordner `ps-snapshot` nach `wp-content/plugins/`.
3. Aktiviere **PS Snapshot** im Menü **Plugins** von WordPress oder ClassicPress.
4. Öffne **Snapshot → Storage Locations** und richte mindestens ein Speicherziel ein.

## Ein Backup erstellen

1. Öffne **Snapshot → Snapshots**.
2. Wähle **Create**.
3. Konfiguriere die gewünschten Datenbanktabellen, Dateien, das Ziel und optional einen Zeitplan.
4. Wähle **Save & Run Backup**.
5. Warte, bis der Fortschritt abgeschlossen ist, und prüfe anschließend das erzeugte Archiv.

## Ein Backup wiederherstellen

1. Öffne **Snapshot → Snapshots**.
2. Wähle beim gewünschten Archiv das Aktionsmenü.
3. Wähle **Restore** und folge dem Assistenten.
4. Prüfe nach der Wiederherstellung die Website und den Admin-Bereich.

Erstelle vor einer Wiederherstellung nach Möglichkeit ein aktuelles Backup und führe den Vorgang bevorzugt zuerst in einer Staging-Umgebung aus.

## Recovery-Skript für Notfälle

Wenn die Website oder der Admin-Bereich nicht mehr erreichbar ist:

1. Lade das [Snapshot Installer-Skript](https://github.com/Power-Source/ps-snapshot-installer) herunter.
2. Lade das Installer-Skript und das Backup-Archiv in das Webroot-Verzeichnis hoch.
3. Rufe `https://deine-domain.tld/snapshot-installer.php` im Browser auf.
4. Folge den Anweisungen des Skripts.

Das Installer-Skript erwartet das Backup-Archiv im selben Verzeichnis. Verwende die Snapshot-v3-Dateikonvention, zum Beispiel `0123456789ab.zip` oder `full_*.zip`.

## Entwicklung

Installiere die JavaScript- und Sass-Abhängigkeiten:

```bash
npm install
```

Erzeuge die Frontend-Assets:

```bash
npm run build
```

Weitere nützliche Befehle:

```bash
# JavaScript bündeln
npm run build:js

# Sass kompilieren
npm run build:sass

# Übersetzungsvorlage aktualisieren
npm run i18n
```

Die englischen Übersetzungsdateien liegen unter [`languages/`](./languages/). Nach einer Änderung der PO-Datei kann die MO-Datei wie folgt erzeugt werden:

```bash
wp i18n make-mo languages/snapshot-en_US.po languages/snapshot-en_US.mo
```

## Mitwirken und Support

- [Quellcode auf GitHub](https://github.com/Power-Source/ps-snapshot)
- [Fehler und Feature-Wünsche melden](https://github.com/Power-Source/ps-snapshot/issues)

Bitte reiche Fehlerberichte mit WordPress-/ClassicPress-Version, PHP-Version, einer Beschreibung der Schritte zur Reproduktion und anonymisierten Log-Auszügen ein.

## Lizenz

PS Snapshot steht unter der [GNU General Public License v2.0 oder neuer](https://www.gnu.org/licenses/gpl-2.0.html).
