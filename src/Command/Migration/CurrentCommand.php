<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\MigrationExtension\Command\Migration;

use GrizzIt\Storage\Common\StorageInterface;
use Ulrack\Command\Common\Command\InputInterface;
use Ulrack\Command\Common\Command\OutputInterface;
use Ulrack\Command\Common\Command\CommandInterface;

class CurrentCommand implements CommandInterface
{
    /**
     * Contains the grouped migrations.
     *
     * @var array
     */
    private $groupedMigrations;

    /**
     * Contains the version storage.
     *
     * @var StorageInterface
     */
    private $versionStorage;

    /**
     * Constructor.
     *
     * @param array $groupedMigrations
     * @param StorageInterface $versionStorage
     */
    public function __construct(
        array $groupedMigrations,
        StorageInterface $versionStorage
    ) {
        $this->groupedMigrations = $groupedMigrations;
        $this->versionStorage = $versionStorage;
    }

    /**
     * Executes the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function __invoke(
        InputInterface $input,
        OutputInterface $output
    ): void {
        foreach ($this->groupedMigrations as $group => $versions) {
            $version = '0.0.0';
            $maxVersion = array_keys($versions);
            $maxVersion = array_pop($maxVersion);
            if ($this->versionStorage->has($group)) {
                $version = $this->versionStorage->get($group);
            }

            $versionString = 'Pool "%s" current version: "%s"';
            if (version_compare($version, $maxVersion) !== 0) {
                $versionString .= ', can be upgraded to: "%s"';
            }

            $output->writeLine(
                sprintf(
                    $versionString . '.',
                    $group,
                    $version,
                    $maxVersion
                )
            );
        }
    }
}
