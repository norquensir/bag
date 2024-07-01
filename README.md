# BAG for Laravel

### 1. Configure the database connection
Configure a new database connection in the `config/database.php` file. Copy the
standard `mysql` connection and create a new `bag` connection.

The following keys are also important in your `.env` file:
```
BAG_DB_HOST=
BAG_DB_PORT=
BAG_DB_DATABASE=
BAG_DB_USERNAME=
BAG_DB_PASSWORD=
```

### 2. Configure the filesystem disk

