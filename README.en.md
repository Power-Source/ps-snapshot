# PS Snapshot

[Deutsch](README.md) | **English**

[![Version](https://img.shields.io/badge/Version-1.0.4-2271b1?style=flat-square)](readme.txt)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777bb4?style=flat-square&logo=php&logoColor=white)
![WordPress](https://img.shields.io/badge/WordPress-up%20to%207.1.0-21759b?style=flat-square&logo=wordpress&logoColor=white)
![ClassicPress](https://img.shields.io/badge/ClassicPress-2.7.4-03768e?style=flat-square)
[![License](https://img.shields.io/badge/License-GPL--2.0--or--later-2ea44f?style=flat-square)](https://www.gnu.org/licenses/gpl-2.0.html)

**Reliable backups and restores for WordPress and ClassicPress.**

PS Snapshot creates database and file backups directly from the administration area. Backups can be stored locally or at remote destinations, scheduled, imported, and restored when needed. The plugin supports multisite installations and includes a separate recovery script for emergencies.

![PS Snapshot administration screen](screenshot.png)

## Features

- Creates backups of database tables, files, or both.
- Stores archives locally, over FTP/SFTP, in Dropbox, or Google Drive.
- Supports one-time and scheduled backups.
- Imports existing backup archives.
- Restores individual snapshots from the administration area.
- Supports WordPress and ClassicPress multisite networks.
- Provides a standalone recovery script when the administration area is unavailable.
- Provides English translations through the `snapshot` text domain.

## Requirements

| Component | Requirement |
| --- | --- |
| PHP | 7.4 or later |
| Database | MySQLi extension |
| Archives | PHP Zip extension |
| PHP runtime | At least 150 seconds recommended |
| CMS | WordPress 4.9+ or ClassicPress 2.0+ |

> [!IMPORTANT]
> Backups are not encrypted. Secure backup archives and remote storage destinations appropriately, especially when they contain sensitive data.

## Installation

1. Download and extract the plugin.
2. Copy the `ps-snapshot` directory to `wp-content/plugins/`.
3. Activate **PS Snapshot** from the **Plugins** menu in WordPress or ClassicPress.
4. Open **Snapshot → Storage Locations** and configure at least one storage destination.

## Creating a backup

1. Open **Snapshot → Snapshots**.
2. Select **Create**.
3. Configure the database tables, files, destination, and optional schedule.
4. Select **Save & Run Backup**.
5. Wait for the progress display to complete, then verify the generated archive.

## Restoring a backup

1. Open **Snapshot → Snapshots**.
2. Open the action menu for the backup you want to restore.
3. Select **Restore** and follow the wizard.
4. Verify the website and administration area after the restore completes.

When possible, create a current backup before restoring and test the process in a staging environment first.

## Recovery script for emergencies

When the website or administration area is no longer accessible:

1. Download the [Snapshot Installer script](https://github.com/Power-Source/ps-snapshot-installer).
2. Upload the installer script and backup archive to the web root directory.
3. Open `https://your-domain.tld/snapshot-installer.php` in a browser.
4. Follow the on-screen instructions.

The installer expects the backup archive in the same directory. Use the Snapshot v3 naming convention, for example `0123456789ab.zip` or `full_*.zip`.

## Development

Install the JavaScript and Sass dependencies:

```bash
npm install
```

Build the frontend assets:

```bash
npm run build
```

Other useful commands:

```bash
# Bundle JavaScript
npm run build:js

# Compile Sass
npm run build:sass

# Update the translation template
npm run i18n
```

The English translation files are in [`languages/`](./languages/). After editing the PO file, compile the MO file with:

```bash
wp i18n make-mo languages/snapshot-en_US.po languages/snapshot-en_US.mo
```

## Contributing and support

- [Source code on GitHub](https://github.com/Power-Source/ps-snapshot)
- [Report bugs and request features](https://github.com/Power-Source/ps-snapshot/issues)

When reporting an issue, include the WordPress or ClassicPress version, PHP version, steps to reproduce the problem, and sanitized log output where available.

## License

PS Snapshot is released under the [GNU General Public License v2.0 or later](https://www.gnu.org/licenses/gpl-2.0.html).
