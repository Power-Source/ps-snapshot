=== PS Snapshot ===
Contributors: PSOURCE
Tags: multisite, snapshot, backups, classicpress-plugin
Requires at least: 4.9
Tested up to: WordPress 6.8.1, ClassicPress 2.6.0
Stable tag: 1.0.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Erstelle blitzschnell Backup-Snapshots deiner ClassicPress-Datenbank auf Knopfdruck – wann immer du sie brauchst!

== Description ==

Dieses Plugin ermöglicht es dir, bei Bedarf schnelle Backup-Snapshots deiner funktionierenden ClassicPress-Datenbank zu erstellen.

= Flexible Speicherung =

Speichere deine Backups einmalig oder nach einem Zeitplan an deinem bevorzugten Ort:

* Lokal auf deinem Server
* Per FTP/SFTP auf externe Server
* Google Drive
* Amazon S3
* Dropbox

Mit der Importier-Funktion kannst du deine Website auch auf einen neuen Webspace umziehen.

= Multisite Support =

Du kannst einzelne Seiten im Netzwerk sichern und wiederherstellen.

**Geplante Features:**
* Zentrale Multisite-Sicherung
* Ermöglichung für Admins von Unterseiten, ihre Websites eigenständig zu sichern

= Notsituation? =

Wenn nichts mehr geht, stellt das [Restore Skript](https://github.com/Power-Source/ps-snapshot-installer) deinen ClassicPress-Snapshot wieder her.

== Installation ==

1. Lade das Plugin herunter und entpacke es
2. Lade den Ordner `ps-snapshot` in das Verzeichnis `/wp-content/plugins/`
3. Aktiviere das Plugin über das „Plugins"-Menü in WordPress/ClassicPress
4. Navigiere zum neuen PS Snapshot Menü und konfiguriere deine Backup-Ziele

== Usage ==

= Ein Backup erstellen =

1. Gehe zu PS Snapshot → Snapshots
2. Klick auf „Snapshot erstellen"
3. Konfiguriere dein Backup (Dateien, Datenbank, Ziel, Häufigkeit)
4. Klick auf „Speichern und Backup ausführen"

= Ein Backup wiederherstellen =

1. Gehe zu PS Snapshot → Snapshots
2. Klick auf die drei Punkte neben deinem Backup
3. Wähle „Wiederherstellen" und folge dem Assistenten

= Mit dem Restore Skript arbeiten =

Wenn du über das WordPress-Admin-Interface nicht zugreifen kannst:

1. Lade das [snapshot-installer.php Skript](https://github.com/Power-Source/ps-snapshot-installer) herunter
2. Lade sowohl das Installer-Skript als auch dein Backup-Archiv in dein Webroot-Verzeichnis hoch
3. Rufe `https://deinedomain.de/snapshot-installer.php` in deinem Browser auf
4. Folge den Anweisungen auf dem Bildschirm

**Wichtig:** Das Installer-Skript erwartet das Backup-Archiv im selben Verzeichnis, benannt nach der Snapshot v3 Dateikonvention (z.B. `[0-9a-f]{12}\.zip` oder `full_*.zip`).

== Beiträge & Bug Reports ==

Wir freuen uns über deine Mitarbeit!

* [GitHub Repository](https://github.com/Power-Source/ps-snapshot) – Beteilige dich am Quellcode
* [Bug Reports](https://github.com/Power-Source/ps-snapshot/issues) – Melde Fehler und Probleme

== Frequently Asked Questions ==

= Kann ich mehrere Backup-Ziele gleichzeitig verwenden? =

Derzeit kannst du pro Snapshot nur ein Ziel auswählen. Du kannst aber mehrere verschiedene Snapshots mit unterschiedlichen Zielen erstellen.

= Ist mein Backup verschlüsselt? =

Nein, die Backups werden nicht verschlüsselt. Wenn du sensible Daten hast, solltest du deine Backup-Speicherorte sichern.

= Wie lange dauert ein Backup? =

Die Dauer hängt von der Größe deiner Website ab. Von wenigen Minuten bis zu mehreren Stunden sind möglich.

= Was ist die Mindest-Ausführungszeit? =

Wir empfehlen eine Mindest-Ausführungszeit von 150 Sekunden. Kontaktiere deinen Hosting-Provider, wenn er niedriger eingestellt ist.

== Anforderungen ==

* PHP 7.4 oder höher
* MySQLi-Modul
* PHP Zip-Modul
* Mindestens 150 Sekunden PHP-Ausführungszeit
* WordPress 4.9+ oder ClassicPress 2.0+

== ChangeLog ==

= 1.0.1 =

* Code Optimierungen

= 1.0.0 =

* Erste öffentliche Version
* Basis-Backup-Funktionalität
* Unterstützung für lokale und Remote-Speicher
* Multisite-Unterstützung

== License ==

PS Snapshot ist lizenziert unter der GPLv2 oder später.
Weitere Informationen: https://www.gnu.org/licenses/gpl-2.0.html

== Weitere Ressourcen ==

* [Dokumentation](https://github.com/Power-Source/ps-snapshot/docs)
* [GitHub Repository](https://github.com/Power-Source/ps-snapshot)
* [Restore Skript](https://github.com/Power-Source/ps-snapshot-installer)