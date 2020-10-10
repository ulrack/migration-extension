<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\MigrationExtension\Component\Generator;

use Ulrack\MigrationExtension\Common\Generator\MatrixGeneratorInterface;
use Ulrack\MigrationExtension\Exception\Matrix\MissingDependencyException;
use Ulrack\MigrationExtension\Exception\Matrix\UnresolvedDependencyException;

class VersionMatrixGenerator implements MatrixGeneratorInterface
{
    /**
     * Generates the matrix.
     *
     * @param array $groupedMigrations
     *
     * @return array[]
     *
     * @throws MissingDependencyException When a pool is referenced which is
     * unavailable.
     * @throws UnresolvedDependencyException When there are dependency which can
     * not be resolved.
     */
    public function __invoke(array $groupedMigrations): array
    {
        $groups = array_keys($groupedMigrations);
        $matrix = [array_combine(
            $groups,
            array_fill(0, count($groups), '0.0.0')
        )];

        $lastMatrixKey = 0;
        $newMatrixGroup = $matrix[0];

        while (count($groupedMigrations) > 0) {
            $addition = false;
            foreach ($groupedMigrations as $key => $configurationGroup) {
                foreach ($configurationGroup as $index => $configuration) {
                    if (isset($configuration['dependant'])) {
                        $canAdd = true;
                        foreach ($configuration['dependant'] as $dependant) {
                            if (!in_array($dependant['pool'], $groups)) {
                                throw new MissingDependencyException(
                                    $dependant['pool']
                                );
                            }

                            if (
                                version_compare(
                                    $matrix[$lastMatrixKey][$dependant['pool']],
                                    $dependant['version']
                                ) === -1
                            ) {
                                $canAdd = false;
                                break;
                            }
                        }

                        if ($canAdd) {
                            $newMatrixGroup[$configuration['pool']] = $configuration['version'];
                            $addition = true;
                            unset($configurationGroup[$index]);
                        }

                        break;
                    }

                    $newMatrixGroup[$configuration['pool']] = $configuration['version'];
                    $addition = true;
                    unset($configurationGroup[$index]);
                    break;
                }

                $groupedMigrations[$key] = $configurationGroup;
                if (count($configurationGroup) === 0) {
                    unset($groupedMigrations[$key]);
                }
            }

            if ($addition === false) {
                throw new UnresolvedDependencyException($groupedMigrations);
            }

            $matrix[] = $newMatrixGroup;
            $lastMatrixKey++;
        }

        return $matrix;
    }
}
