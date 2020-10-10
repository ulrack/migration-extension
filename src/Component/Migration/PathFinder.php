<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\MigrationExtension\Component\Migration;

use GrizzIt\Storage\Common\StorageInterface;
use Ulrack\MigrationExtension\Common\Migration\PathFinderInterface;
use Ulrack\MigrationExtension\Common\Version\RangeSelectorInterface;
use Ulrack\MigrationExtension\Exception\Migration\UndefinedVersionException;

class PathFinder implements PathFinderInterface
{
    /**
     * Contains the versions that are installed for the current path.
     *
     * @var array
     */
    private $versionInstalled;

    /**
     * Contains the grouped migrations.
     *
     * @var array
     */
    private $groups;

    /**
     * Contains the range selector.
     *
     * @var RangeSelectorInterface
     */
    private $rangeSelector;

    /**
     * Contains the restoration configuration of the last generated path.
     *
     * @var array
     */
    private $restoreConfig = [];

    /**
     * Constructor.
     *
     * @param RangeSelectorInterface $rangeSelector
     */
    public function __construct(RangeSelectorInterface $rangeSelector)
    {
        $this->rangeSelector = $rangeSelector;
    }

    /**
     * Retrieves the restoration configuration of the last fetched path.
     *
     * @return array
     */
    public function getRestoreConfig(): array
    {
        return $this->restoreConfig;
    }

    /**
     * Constructs the migration path.
     *
     * @param array $versionConfig
     * @param StorageInterface $versionStorage
     *
     * @return array
     */
    public function __invoke(
        array $versionConfig,
        StorageInterface $versionStorage,
        array $groups
    ): array {
        $this->versionInstalled = iterator_to_array($versionStorage);
        $this->groups = $groups;

        foreach (array_keys($this->groups) as $groupKey) {
            if (!isset($this->versionInstalled[$groupKey])) {
                $this->versionInstalled[$groupKey] = '0.0.0';
            }
        }

        $this->restoreConfig = $this->versionInstalled;

        $migrationPath = [];
        foreach ($versionConfig as $pool => $version) {
            $migrationPath = array_merge(
                $migrationPath,
                $this->getMigrationPath(
                    $pool,
                    $version,
                    $this->versionInstalled[$pool] > $version
                )
            );
        }

        return $migrationPath;
    }

    /**
     * Determines the migration path for a single pool, version combination.
     *
     * @param string $pool
     * @param string $version
     * @param bool $upgrade
     *
     * @return array
     */
    private function getMigrationPath(
        string $pool,
        string $version,
        bool $checkReverse
    ): array {
        $versions = array_keys($this->groups[$pool]);
        if (!in_array($version, array_merge($versions, ['0.0.0']))) {
            throw new UndefinedVersionException($pool, $version, $versions);
        }

        if (
            version_compare(
                $this->versionInstalled[$pool],
                $version,
                $checkReverse ? '<=' : '>='
            )
        ) {
            return [];
        }

        $versions = $this->rangeSelector->__invoke(
            $versions,
            $checkReverse ? $version : $this->versionInstalled[$pool],
            $checkReverse ? $this->versionInstalled[$pool] : $version
        );

        if ($checkReverse) {
            $versions = array_reverse($versions);
        }

        return $this->resolveMigrationPath($versions, $checkReverse, $pool);
    }

    /**
     * Retrieves the version before the current one.
     *
     * @param string $pool
     * @param string $version
     *
     * @return string
     */
    private function getPreviousVersion(string $pool, string $version): string
    {
        $versions = array_keys($this->groups[$pool]);
        $key = array_search($version, $versions);

        return $key > 0 ? $versions[$key - 1] : '0.0.0';
    }

    /**
     * Resolves the migration path.
     *
     * @param array $versions
     * @param boolean $checkReverse
     * @param string $pool
     *
     * @return array
     */
    private function resolveMigrationPath(
        array $versions,
        bool $checkReverse,
        string $pool
    ): array {
        $migrationPath = [];
        foreach ($versions as $migration) {
            $toVersion = $checkReverse ?
                $this->getPreviousVersion($pool, $migration) :
                $migration;

            $migrationPath = array_merge(
                $migrationPath,
                $checkReverse
                    ? $this->getExternalDependencies($pool, $toVersion)
                    : $this->getDependencies($pool, $toVersion)
            );

            $this->versionInstalled[$pool] = $toVersion;
            $migrationPath[] = [
                'pool' => $pool,
                'version' => $migration,
                'to_version' => $toVersion,
                'method' => $checkReverse ? 'revert' : 'apply'
            ];
        }

        return $migrationPath;
    }

    /**
     * Retrieves the external dependencies for the current migration.
     *
     * @param string $pool
     * @param string $version
     *
     * @return array
     */
    private function getExternalDependencies(
        string $pool,
        string $version
    ): array {
        $dependants = [];
        foreach ($this->groups as $groupPool => $migrations) {
            if ($groupPool !== $pool) {
                foreach ($migrations as $migration) {
                    foreach ($migration['dependant'] ?? [] as $dependant) {
                        if (
                            $dependant['pool'] === $pool &&
                            version_compare(
                                $version,
                                $dependant['version'],
                                '<'
                            )
                        ) {
                            $dependants = array_merge(
                                $dependants,
                                $this->getMigrationPath(
                                    $groupPool,
                                    $this->getPreviousVersion(
                                        $groupPool,
                                        $migration['version']
                                    ),
                                    true
                                )
                            );
                        }
                    }
                }
            }
        }

        return $dependants;
    }

    /**
     * Retrieves the dependencies for the current migration.
     *
     * @param string $pool
     * @param string $version
     *
     * @return array
     */
    private function getDependencies(string $pool, string $version): array
    {
        $dependants = [];
        if (isset($this->groups[$pool][$version]['dependant'])) {
            foreach ($this->groups[$pool][$version]['dependant'] ?? [] as $dependant) {
                $dependants = array_merge(
                    $dependants,
                    $this->getMigrationPath(
                        $dependant['pool'],
                        $dependant['version'],
                        false
                    )
                );
            }
        }

        return $dependants;
    }
}
