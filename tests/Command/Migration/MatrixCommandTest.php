<?php

namespace Ulrack\MigrationExtension\Tests\Command\Migration;

use PHPUnit\Framework\TestCase;
use Ulrack\Command\Common\Command\InputInterface;
use Ulrack\Command\Common\Command\OutputInterface;
use Ulrack\MigrationExtension\Command\Migration\MatrixCommand;

/**
 * @coversDefaultClass \Ulrack\MigrationExtension\Command\Migration\MatrixCommand
 */
class MatrixCommandTest extends TestCase
{
    /**
     * @covers ::__invoke
     * @covers ::__construct
     *
     * @return void
     */
    public function testInvoke(): void
    {
        $matrix = [
            ['foo' => '0.0.0', 'bar' => '0.0.0'],
            ['foo' => '1.0.0', 'bar' => '1.0.0']
        ];

        $subject = new MatrixCommand($matrix);

        $output = $this->createMock(OutputInterface::class);

        $output->expects(static::once())
            ->method('outputTable')
            ->with(['foo', 'bar'], $matrix);

        $subject->__invoke($this->createMock(InputInterface::class), $output);
    }

    /**
     * @covers ::__invoke
     * @covers ::__construct
     *
     * @return void
     */
    public function testInvokeNoMatrix(): void
    {
        $subject = new MatrixCommand([]);
        $output = $this->createMock(OutputInterface::class);

        $output->expects(static::once())
            ->method('outputBlock');

        $subject->__invoke($this->createMock(InputInterface::class), $output);
    }
}
