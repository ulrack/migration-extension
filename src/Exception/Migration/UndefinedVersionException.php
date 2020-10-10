<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\MigrationExtension\Exception\Migration;

use Exception;

class UndefinedVersionException extends Exception
{
    /**
     * Constructor.
     *
     * @param string $pool
     * @param string $version
     * @param string[] $versions
     */
    public function __construct(string $pool, string $version, array $versions)
    {
        parent::__construct(
            sprintf(
                'Can not find version %s for pool "%s".' .
                PHP_EOL .
                'Available versions: %s',
                $version,
                $pool,
                implode(', ', $versions)
            )
        );
    }
}
