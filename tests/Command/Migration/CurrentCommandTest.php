<?php

namespace Ulrack\MigrationExtension\Tests\Command\Migration;

use PHPUnit\Framework\TestCase;
use GrizzIt\Storage\Component\ObjectStorage;
use GrizzIt\Command\Common\Command\InputInterface;
use GrizzIt\Command\Common\Command\OutputInterface;
use Ulrack\MigrationExtension\Command\Migration\CurrentCommand;

/**
 * @coversDefaultClass \Ulrack\MigrationExtension\Command\Migration\CurrentCommand
 */
class CurrentCommandTest extends TestCase
{
    /**
     * @covers ::__invoke
     * @covers ::__construct
     *
     * @return void
     */
    public function testInvoke(): void
    {
        $groupedMigrations = [
            'foo' => ['1.0.0' => [], '2.0.0' => []],
            'bar' => ['1.0.0' => []]
        ];

        $versionStorage = new ObjectStorage([
            'foo' => '1.0.0',
            'bar' => '1.0.0'
        ]);

        $subject = new CurrentCommand($groupedMigrations, $versionStorage);

        $output = $this->createMock(OutputInterface::class);

        $output->expects(static::exactly(2))->method('writeLine');

        $subject->__invoke($this->createMock(InputInterface::class), $output);
    }
}
