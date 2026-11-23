# Client Tracker

A PHP and MySQL app for keeping client details and their automation records.

Users can register and log in, add or update clients, and manage each client's automations. An automation can include an image. Deleted automations appear in the Recycle Bin with the deletion details and can be restored while the client is active.

## Requirements

- PHP with PDO MySQL
- MySQL or MariaDB
- Apache (XAMPP works)

## Database

Import `sql/migration.sql` into phpMyAdmin for a new database. It creates `client_tracker_db` and the tables used by the app.

For an existing database, use the matching SQL upgrade file in `sql/`. Back up the database first; do not import the upgrade files along with the new-install migration.

The default connection is `127.0.0.1`, database `client_tracker_db`, user `root`, and a blank password. Set `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` to use different credentials.

## Run locally

Place the project folder in Apache's document root, start Apache and MySQL, then open `register.php` through localhost. The `uploads/` folder must be writable by PHP for image uploads.

## Folders

- `core/` - database connection, login checks, form handling, and database queries
- `sql/` - database setup and upgrade scripts
- `uploads/` - uploaded automation images
- `styles.css` - page styles
