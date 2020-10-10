<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\MigrationExtension\Exception\Matrix;

use Exception;

class MissingDependencyException extends Exception
{
    /**
     * Constructor.
     *
     * @param string $pool
     */
    public function __construct(string $pool)
    {
        parent::__construct(
            sprintf(
                'Could not find pool for dependency %s',
                $pool
            )
        );
    }
}
