# BAG for Laravel

### 1. Configure the database connection
Configure a new database connection in the `config/database.php` file. You can use the default `mysql`
connection as example and tweak the settings to your liking.

Use these keys in your database configuration, and use them in your `.env` file. These are required to import
the BAG data.
```
BAG_DB_HOST=
BAG_DB_PORT=
BAG_DB_DATABASE=
BAG_DB_USERNAME=
BAG_DB_PASSWORD=
```

### 2. Configure the filesystem disk
Configure a new filesystem disk in the  `config/filesystems.php` file. This storage is used to store all the
ZIP and XML files.

Example:
```
'local' => [
    'driver' => 'local',
    'root' => storage_path('bag'),
    'throw' => false,
],
```

### 3. Install the package's necessary files
> Before you can run the installation, make sure you have the `jobs` table migrated in your main project.

After you've configured all necessary settings, you can run the `bag:install` command. This will run the
migrations you need.

---

### Configuration file
> Run `php artisan vendor:publish --provider="Norquensir\Bag\BagServiceProvider"` to publish the package
> config file

In the config file you can set the following keys:
- routes
  - active: Use standard BAG routes or not
  - middleware: Set which middlewares should be active on all routes
  - prefix: Set prefix for BAG routes
