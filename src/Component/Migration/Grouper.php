<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\MigrationExtension\Component\Migration;

use Ulrack\MigrationExtension\Common\Migration\GrouperInterface;

class Grouper implements GrouperInterface
{
    /**
     * Sorts the migration configuration.
     *
     * @param array $migrations
     *
     * @return array
     */
    private function sortMigrationConfiguration(array $migrations): array
    {
        usort(
            $migrations,
            function (array $left, array $right): int {
                return version_compare($left['version'], $right['version']);
            }
        );

        return $migrations;
    }

    /**
     * Groups the migrations.
     *
     * @param array $migrations
     *
     * @return array
     */
    public function __invoke(array $migrations): array
    {
        $migrations = $this->sortMigrationConfiguration($migrations);
        $grouped = [];
        foreach ($migrations as $configuration) {
            $grouped[
                $configuration['pool']
            ][$configuration['version']] = $configuration;
        }

        return $grouped;
    }
}
