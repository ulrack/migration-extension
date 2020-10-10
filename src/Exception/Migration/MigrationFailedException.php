<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\MigrationExtension\Exception\Migration;

use Exception;
use Throwable;

class MigrationFailedException extends Exception
{
    /**
     * Constructor.
     *
     * @param array $installed
     * @param array $restoreConfig
     * @param Throwable $previous
     */
    public function __construct(
        array $installed,
        array $restoreConfig,
        Throwable $previous
    ) {
        $installedText = [];
        foreach ($installed as $pool => $version) {
            $installedText[] = $pool . ':' . $version;
        }

        $restoreConfigText = 'migration migrate';
        foreach ($restoreConfig as $pool => $version) {
            $restoreConfigText .= sprintf(
                ' --pool[]=%s --version[]=%s',
                $pool,
                $version
            );
        }

        parent::__construct(
            sprintf(
                'Migration failed.' . PHP_EOL .
                'The following versions have been installed: %s' . PHP_EOL .
                'To revert these changes, run the following command: ' . PHP_EOL .
                '%s',
                implode(', ', $installedText),
                $restoreConfigText
            ),
            1,
            $previous
        );
    }
}
