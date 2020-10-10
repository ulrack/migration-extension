<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\MigrationExtension\Exception\Migration;

use Exception;

class MisconfiguredParameterException extends Exception
{
    /**
     * Constructor.
     *
     * @param mixed $pool
     * @param mixed $version
     */
    public function __construct($pool, $version)
    {
        parent::__construct(
            sprintf(
                'There must an equal amount of pools and versions passed.' .
                PHP_EOL .
                'Pools: %s, versions: %s',
                json_encode($pool),
                json_encode($version)
            )
        );
    }
}
