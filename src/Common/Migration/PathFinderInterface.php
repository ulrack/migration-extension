<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\MigrationExtension\Common\Migration;

use GrizzIt\Storage\Common\StorageInterface;

interface PathFinderInterface
{
    /**
     * Constructs the migration path.
     *
     * @param array $versionConfig
     * @param StorageInterface $versionStorage
     *
     * @return array
     */
    public function __invoke(
        array $versionConfig,
        StorageInterface $versionStorage,
        array $groups
    ): array;

    /**
     * Retrieves the restoration configuration of the last fetched path.
     *
     * @return array
     */
    public function getRestoreConfig(): array;
}
