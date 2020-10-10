<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\MigrationExtension\Common\Migration;

interface GrouperInterface
{
    /**
     * Groups the migrations.
     *
     * @param array $migrations
     *
     * @return array
     */
    public function __invoke(array $migrations): array;
}
