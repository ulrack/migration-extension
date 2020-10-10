<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\MigrationExtension\Common;

interface MigrationInterface
{
    /**
     * Applies the migration.
     *
     * @return void
     */
    public function apply(): void;

    /**
     * Reverts the migration.
     *
     * @return void
     */
    public function revert(): void;
}
