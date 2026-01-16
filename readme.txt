=== PS Snapshot ===
Contributors: PSOURCE
Tags: multisite, snapshot, backups, classicpress-plugin
Requires at least: 4.9
Tested up to: WordPress 6.8.1, ClassicPress 2.6.0
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Dieses Plugin ermöglicht es Dir, bei Bedarf schnelle Backup-Snapshots Deiner funktionierenden ClassicPress-Datenbank zu erstellen.

Speichere Backups einmalig oder nach einem Zeitplan und entscheide wo Du die Backup-Daten sicherst.
Du kannst Backupdaten lokal auf dem eigenen Server speichern, per FTP/sFTP hochladen, auf Google Drive, Amazon s3 oder DropBox als Speicherort für Deine Backups wählen.
Durch die Importier-Funktion kannst Du so auch eine Seite auf einen neuen Webspace umziehen.

= Multisite =

Du kannst einzelne Seiten im Netzwerk sichern.

Wir planen für die Zukunft:

Zentrale Multisite-Sicherung
Möglichkeiten das Admins von Unterseiten selbstständig ihre Webseiten sichern können.

Du hast weitere Ideen und Vorschläge oder einen Fehler gefunden?

* [PS SnapShot auf GitHub](https://github.com/Power-Source/ps-snapshot) - Hier kannst Dich am Quellcode beteiligen
* [Melde Fehler](https://github.com/Power-Source/ps-snapshot/issues) - Damit hilfst Du uns wirklich enorm!

== Description ==

PS Snapshot legt auf Knopfdruck ein Backup Deiner ClassicPress Webseite an. Wenn alles schiefgeht kannst Du so Deine Webseite wieder in den gesicherten Zustand bringen.

= Restore Skript =

Wenn nichts mehr geht, stellt [dieses Skript](https://github.com/Power-Source/ps-snapshot-installer) Deinen CP-Snapshot wieder her

Um ein Snapshot Archiv wiederherzustellen, benötigen wir zwei Dinge:

snapshot-installer.php
Aktuelles Backup-Archiv :)
Sobald Du beide hast, kopiere sie in ein Zielverzeichnis Deiner Wahl (natürlich in Deinem Webroot). Der Installer erwartet das Sicherungsarchiv im selben Verzeichnis und benannt nach dem vollständigen Backup-Archiv von Snapshot v3 Dateikonvention (z. B. [0-9a-f]{12}\.zip oder full_*.zip).

Rufe anschliessend snapshot-installer.php über Deinen Browser auf (z.B: https://meinehompage/snapshot-installer.php)

[RESTORE SCRIPT](https://github.com/Power-Source/snapshot-installer-1.0.1)



== ChangeLog ==

= 1.0.0 =

* release