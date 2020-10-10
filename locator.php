<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

use GrizzIt\Configuration\Component\Configuration\PackageLocator;
use Ulrack\MigrationExtension\Common\UlrackMigrationExtensionPackage;

PackageLocator::registerLocation(
    __DIR__,
    UlrackMigrationExtensionPackage::PACKAGE_NAME
);
