<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\MigrationExtension\Common\Version;

interface RangeSelectorInterface
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
    ): array;
}
