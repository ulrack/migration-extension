<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\MigrationExtension\Exception\Matrix;

use Exception;

class UnresolvedDependencyException extends Exception
{
    /**
     * Constructor.
     *
     * @param array $groups
     */
    public function __construct(array $groups)
    {
        parent::__construct(
            sprintf(
                'Can not construct matrix, dependencies can not be resolved %s',
                json_encode($groups)
            )
        );
    }
}
