<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\MigrationExtension\Command\Migration;

use Throwable;
use GrizzIt\Storage\Common\StorageInterface;
use GrizzIt\Command\Common\Command\InputInterface;
use GrizzIt\Command\Common\Command\OutputInterface;
use GrizzIt\Command\Common\Command\CommandInterface;
use GrizzIt\Services\Common\Factory\ServiceFactoryInterface;
use Ulrack\MigrationExtension\Common\Migration\PathFinderInterface;
use Ulrack\MigrationExtension\Exception\Migration\MigrationFailedException;
use Ulrack\MigrationExtension\Exception\Migration\MisconfiguredParameterException;

class MigrateCommand implements CommandInterface
{
    /**
     * Contains the matrix.
     *
     * @var array
     */
    private array $matrix;

    /**
     * Contains the version storage.
     *
     * @var StorageInterface
     */
    private StorageInterface $versionStorage;

    /**
     * Contains the grouped version of migration pools.
     *
     * @var array
     */
    private array $groups;

    /**
     * Contains the service factory to construct the migrations.
     *
     * @var ServiceFactoryInterface
     */
    private ServiceFactoryInterface $serviceFactory;

    /**
     * Contains the path finder for determining the migration path.
     *
     * @var PathFinderInterface
     */
    private PathFinderInterface $pathFinder;

    /**
     * Constructor.
     *
     * @param array $groups
     * @param array $matrix
     * @param StorageInterface $versionStorage
     * @param ServiceFactoryInterface $serviceFactory
     * @param PathFinderInterface $pathFinder
     */
    public function __construct(
        array $groups,
        array $matrix,
        StorageInterface $versionStorage,
        ServiceFactoryInterface $serviceFactory,
        PathFinderInterface $pathFinder
    ) {
        $this->groups = $groups;
        $this->matrix = $matrix;
        $this->versionStorage = $versionStorage;
        $this->serviceFactory = $serviceFactory;
        $this->pathFinder = $pathFinder;
    }

    /**
     * Executes the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     *
     * @throws MigrationFailedException When the execution of the migration fails.
     */
    public function __invoke(
        InputInterface $input,
        OutputInterface $output
    ): void {
        $migrationPath = $this->pathFinder->__invoke(
            $this->getVersionConfig($input),
            $this->versionStorage,
            $this->groups
        );

        if (count($migrationPath) > 0) {
            $result = ['continue' => 'yes'];
            if (!$input->isSetFlag('no-interaction')) {
                $description = '';

                foreach ($migrationPath as $migrationStep) {
                    $description .= sprintf(
                        '%s %s:%s',
                        ucfirst($migrationStep['method']),
                        $migrationStep['pool'],
                        $migrationStep['version'] !== $migrationStep['to_version'] ?
                            sprintf(
                                '%s to %s',
                                $migrationStep['version'],
                                $migrationStep['to_version']
                            ) : $migrationStep['version']
                    ) . PHP_EOL;
                }
                $form = $output
                    ->getFormGenerator()
                    ->init(
                        'The following migrations will be executed.',
                        $description
                    )->addAutocompletingField('continue', ['yes', 'no'], false)
                    ->getForm();
                $form->render();
                $result = $form->getInput();
            }

            if ($result['continue'] === 'yes') {
                foreach ($migrationPath as $migration) {
                    try {
                        $migrationConfig = $this->groups[
                                $migration['pool']
                            ][$migration['version']];

                        $output->writeLine(
                            sprintf(
                                'Executing "%s" for migration %s:%s %s',
                                $migration['method'],
                                $migration['pool'],
                                $migration['version'],
                                $migrationConfig['description']
                            )
                        );

                        $method = $migration['method'];
                        $this->serviceFactory->create(
                            $migrationConfig['service']
                        )->$method();

                        $this->versionStorage->set(
                            $migration['pool'],
                            $migration['to_version']
                        );
                    } catch (Throwable $exception) {
                        throw new MigrationFailedException(
                            iterator_to_array($this->versionStorage),
                            $this->pathFinder->getRestoreConfig(),
                            $exception
                        );
                    }
                }
            }
        }
    }

    /**
     * Retrieves the version configuration.
     *
     * @param InputInterface $input
     *
     * @return array
     *
     * @throws MisconfiguredParameterException When no migration configuration is passed.
     */
    private function getVersionConfig(InputInterface $input): array
    {
        $pool = null;
        $version = null;
        if ($input->isSetFlag('latest')) {
            return array_pop($this->matrix);
        } elseif (
            $input->hasParameter('pool') &&
            $input->hasParameter('version')
        ) {
            $pool = $input->getParameter('pool');
            $version = $input->getParameter('version');
            if (is_string($pool) && is_string($version)) {
                return [$pool => $version];
            } elseif (
                is_array($pool) &&
                is_array($version) &&
                count($pool) === count($version)
            ) {
                return array_combine($pool, $version);
            }
        }

        throw new MisconfiguredParameterException($pool, $version);
    }
}
