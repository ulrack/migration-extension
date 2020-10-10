<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\MigrationExtension\Component\Version;

use Ulrack\MigrationExtension\Common\Version\RangeSelectorInterface;

class RangeSelector implements RangeSelectorInterface
{
    /**
     * Retrieves a range of versions.
     *
     * @param array $versions
     * @param string $lowest
     * @param string $highest
     *
     * @return array
     */
    public function __invoke(
        array $versions,
        string $lowest,
        string $highest
    ): array {
        foreach ($versions as $key => $migration) {
            if (
                version_compare($migration, $lowest, '>') &&
                version_compare($migration, $highest) < 1
            ) {
                continue;
            }

            unset($versions[$key]);
        }

        return $versions;
    }
}
