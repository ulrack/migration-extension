<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\MigrationExtension\Command\Migration;

use GrizzIt\Command\Common\Command\InputInterface;
use GrizzIt\Command\Common\Command\OutputInterface;
use GrizzIt\Command\Common\Command\CommandInterface;

class MatrixCommand implements CommandInterface
{
    /**
     * Contains the matrix.
     *
     * @var array
     */
    private array $matrix;

    /**
     * Constructor.
     *
     * @param array $matrix
     */
    public function __construct(array $matrix)
    {
        $this->matrix = $matrix;
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
        if (count($this->matrix) === 0) {
            $output->outputBlock(
                'Matrix could not be generated, because there are no migrations.'
            );

            return;
        }

        $output->outputTable(
            array_keys($this->matrix[0]),
            $this->matrix
        );
    }
}
