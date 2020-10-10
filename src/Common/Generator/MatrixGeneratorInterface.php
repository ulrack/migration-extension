<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\MigrationExtension\Common\Generator;

interface MatrixGeneratorInterface
{
    /**
     * Generates the matrix.
     *
     * @param array $groupedMigrations
     *
     * @return array[]
     */
    public function __invoke(array $groupedMigrations): array;
}
