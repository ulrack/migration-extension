# Ulrack Migration Extension - Create a Migration

After the package has been installed, it can be used to create setup scripts
for projects.

## Defining a migration

Migration er being defined in the `configuration/migrations` directory of a
project. These files are described by the `migrations.schema.json` schema.
Migrations are defined by pools, these can be used to determine dependencies of
migrations on one another. The migrations also need a service, a version and a
description. A simple example of a migration file would be the following:

```json
{
    "$schema": "migrations.schema.json",
    "version": "1.0.0",
    "service": "services.migrations.main.1.0.0",
    "description": "Creates the initial database.",
    "pool": "main"
}
```

When another migration has a dependency on this migration, the configuration
would look like the following example:

```json
{
    "$schema": "migrations.schema.json",
    "version": "1.0.0",
    "service": "services.migrations.tables.1.0.0",
    "description": "Creates the initial tables of the project.",
    "pool": "tables",
    "dependant": [
        {
            "pool": "main",
            "version": "1.0.0"
        }
    ]
}
```

The dependency are used to sort the migrations in the correct order. So in this
case the `main` 1.0.0 migration should be executed befor the `tables` 1.0.0
migration. When downgrades are performed, this logic is inverted, so the `revert`
method of `tables` 1.0.0 is executed, prior to that of `main`.

## Defining the service

A service declaration must be created for the migrations. A database migration
service file could look like the following:

```json
{
    "services": {
        "migrations.main.1.0.0": {
            "class": "\\MyVendor\\MyProject\\MigrationMain100",
            "parameters": {
                "connection": "@{database-connections.main}"
            }
        }
    }
}
```

This service is then used to fetch the correct object for the defined migration.

## Creating the migration

Migration must extend the `Ulrack\MigrationExtension\Common\MigrationInterface`.
So the class for this migration will look like this:
```php
<?php

namespace MyVendor\MyProject;

use GrizzIt\Dbal\Common\ConnectionInterface;
use Ulrack\MigrationExtension\Common\MigrationInterface;
use GrizzIt\Dbal\Sql\Component\Query\Database\DropDatabaseQuery;
use GrizzIt\Dbal\Sql\Component\Query\Database\CreateDatabaseQuery;

class MigrationMain100 implements MigrationInterface
{
    /**
     * Contains the database connection
     *
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * Constructor.
     *
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Applies the migration.
     *
     * @return void
     */
    public function apply(): void
    {
        $this->connection->query(new CreateDatabaseQuery('my-database'));
    }

    /**
     * Reverts the migration.
     *
     * @return void
     */
    public function revert(): void
    {
        $this->connection->query(new DropDatabaseQuery('my-database'));
    }
}

```

## Testing the migration

After the cach has been cleared for the project, the migrations are ready to be
tested. A set of commands has been added to test and execute the migrations.

To check the current status of the migrations, execute the following command:
```
bin/application migration current
```

To get an overview of the logical steps the application will take, execute the
matrix command. This will display a matrix of all migrations.
```
bin/application migration matrix
```

To execute the migrations, execute the migrate command:
```
bin/application migration migrate --latest
```

By adding the `--latest` flag, all migrations will be executed to get to the
latest version.

It is also possible to only upgrade a set of pools, by providing them through
the `--pool` and `--version` commands.
```
bin/application migration migrate --pool[]=main --version[]=1.0.0
```

If the migrations should be done without asking for confirmation, pass the `--ni`
or `--no-interaction` flag.

## Further reading

[Back to usage index](index.md)

[Installation](installation.md)
