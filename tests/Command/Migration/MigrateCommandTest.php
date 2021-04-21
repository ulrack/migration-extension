<?php

namespace Ulrack\MigrationExtension\Tests\Command\Migration;

use Exception;
use PHPUnit\Framework\TestCase;
use GrizzIt\Storage\Common\StorageInterface;
use GrizzIt\Storage\Component\ObjectStorage;
use GrizzIt\Cli\Common\Element\FormInterface;
use GrizzIt\Command\Common\Command\InputInterface;
use GrizzIt\Command\Common\Command\OutputInterface;
use GrizzIt\Cli\Common\Generator\FormGeneratorInterface;
use Ulrack\MigrationExtension\Common\MigrationInterface;
use GrizzIt\Services\Common\Factory\ServiceFactoryInterface;
use Ulrack\MigrationExtension\Command\Migration\MigrateCommand;
use Ulrack\MigrationExtension\Common\Migration\PathFinderInterface;
use Ulrack\MigrationExtension\Exception\Migration\MigrationFailedException;
use Ulrack\MigrationExtension\Exception\Migration\MisconfiguredParameterException;

/**
 * @coversDefaultClass \Ulrack\MigrationExtension\Command\Migration\MigrateCommand
 * @covers \Ulrack\MigrationExtension\Exception\Migration\MisconfiguredParameterException
 * @covers \Ulrack\MigrationExtension\Exception\Migration\MigrationFailedException
 */
class MigrateCommandTest extends TestCase
{
    /**
     * @covers ::getVersionConfig
     * @covers ::__invoke
     * @covers ::__construct
     *
     * @return void
     */
    public function testInvokeLatest(): void
    {
        $groups = [
            'foo' => [
                '1.0.0' => [
                    'description' => 'Apply foo',
                    'service' => 'foo-service'
                ]
            ]
        ];
        $matrix = [
            [
                '0.0.0'
            ],
            [
                '1.0.0'
            ]
        ];

        $versionStorage = $this->createMock(StorageInterface::class);
        $serviceFactory = $this->createMock(ServiceFactoryInterface::class);
        $pathFinder = $this->createMock(PathFinderInterface::class);
        $subject = new MigrateCommand(
            $groups,
            $matrix,
            $versionStorage,
            $serviceFactory,
            $pathFinder
        );

        $pathFinder->expects(static::once())
            ->method('__invoke')
            ->willReturn(
                [
                    [
                        'pool' => 'foo',
                        'version' => '1.0.0',
                        'to_version' => '1.0.0',
                        'method' => 'apply'
                    ]
                ]
            );

        $migration = $this->createMock(MigrationInterface::class);

        $migration->expects(static::once())
            ->method('apply');

        $serviceFactory->expects(static::once())
            ->method('create')
            ->with('foo-service')
            ->willReturn($migration);

        $output = $this->createMock(OutputInterface::class);
        $input = $this->createMock(InputInterface::class);

        $input->expects(static::exactly(2))
            ->method('isSetFlag')
            ->withConsecutive(['latest'], ['no-interaction'])
            ->willReturn(true);

        $versionStorage->expects(static::once())
            ->method('set')
            ->with('foo', '1.0.0');

        $subject->__invoke(
            $input,
            $output
        );
    }

    /**
     * @covers ::getVersionConfig
     * @covers ::__invoke
     * @covers ::__construct
     *
     * @return void
     */
    public function testInvokeInteraction(): void
    {
        $groups = [
            'foo' => [
                '1.0.0' => [
                    'description' => 'Apply foo',
                    'service' => 'foo-service'
                ]
            ]
        ];
        $matrix = [
            [
                '0.0.0'
            ],
            [
                '1.0.0'
            ]
        ];

        $versionStorage = $this->createMock(StorageInterface::class);
        $serviceFactory = $this->createMock(ServiceFactoryInterface::class);
        $pathFinder = $this->createMock(PathFinderInterface::class);
        $subject = new MigrateCommand(
            $groups,
            $matrix,
            $versionStorage,
            $serviceFactory,
            $pathFinder
        );

        $pathFinder->expects(static::once())
            ->method('__invoke')
            ->willReturn(
                [
                    [
                        'pool' => 'foo',
                        'version' => '1.0.0',
                        'to_version' => '0.0.0',
                        'method' => 'revert'
                    ]
                ]
            );

        $output = $this->createMock(OutputInterface::class);
        $input = $this->createMock(InputInterface::class);

        $input->expects(static::exactly(2))
            ->method('isSetFlag')
            ->withConsecutive(['latest'], ['no-interaction'])
            ->willReturn(false);

        $input->expects(static::exactly(2))
            ->method('hasParameter')
            ->withConsecutive(['pool'], ['version'])
            ->willReturn(true);

        $input->expects(static::exactly(2))
            ->method('getParameter')
            ->withConsecutive(['pool'], ['version'])
            ->willReturnOnConsecutiveCalls('foo', '0.0.0');

        $form = $this->createMock(FormInterface::class);

        $form->expects(static::once())->method('render');

        $form->expects(static::once())
            ->method('getInput')
            ->willReturn(['continue' => 'no']);

        $formGenerator = $this->createMock(FormGeneratorInterface::class);
        $formGenerator->expects(static::once())
            ->method('init')
            ->willReturnSelf();

        $formGenerator->expects(static::once())
            ->method('addAutoCompletingField')
            ->willReturnSelf();

        $formGenerator->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

        $output->expects(static::once())
            ->method('getFormGenerator')
            ->willReturn($formGenerator);

        $subject->__invoke(
            $input,
            $output
        );
    }

    /**
     * @covers ::getVersionConfig
     * @covers ::__invoke
     * @covers ::__construct
     *
     * @return void
     */
    public function testInvokeFailure(): void
    {
        $groups = [
            'foo' => [
                '1.0.0' => [
                    'description' => 'Apply foo',
                    'service' => 'foo-service'
                ]
            ]
        ];
        $matrix = [
            [
                '0.0.0'
            ],
            [
                '1.0.0'
            ]
        ];

        $versionStorage = new ObjectStorage(['foo' => '0.0.1']);
        $serviceFactory = $this->createMock(ServiceFactoryInterface::class);
        $pathFinder = $this->createMock(PathFinderInterface::class);
        $subject = new MigrateCommand(
            $groups,
            $matrix,
            $versionStorage,
            $serviceFactory,
            $pathFinder
        );

        $pathFinder->expects(static::once())
            ->method('__invoke')
            ->willReturn(
                [
                    [
                        'pool' => 'foo',
                        'version' => '1.0.0',
                        'to_version' => '1.0.0',
                        'method' => 'apply'
                    ]
                ]
            );

        $pathFinder->expects(static::once())
            ->method('getRestoreConfig')
            ->willReturn(['foo' => '0.0.1']);

        $serviceFactory->expects(static::once())
            ->method('create')
            ->with('foo-service')
            ->willThrowException(new Exception());

        $output = $this->createMock(OutputInterface::class);
        $input = $this->createMock(InputInterface::class);

        $input->expects(static::exactly(2))
            ->method('isSetFlag')
            ->withConsecutive(['latest'], ['no-interaction'])
            ->willReturnOnConsecutiveCalls(false, true);

        $input->expects(static::exactly(2))
            ->method('hasParameter')
            ->withConsecutive(['pool'], ['version'])
            ->willReturn(true);

        $input->expects(static::exactly(2))
            ->method('getParameter')
            ->withConsecutive(['pool'], ['version'])
            ->willReturnOnConsecutiveCalls(['foo'], ['1.0.0']);

        $this->expectException(MigrationFailedException::class);

        $subject->__invoke(
            $input,
            $output
        );
    }

    /**
     * @covers ::getVersionConfig
     * @covers ::__invoke
     * @covers ::__construct
     *
     * @return void
     */
    public function testInvokeParameterError(): void
    {
        $groups = [];
        $matrix = [];

        $versionStorage = $this->createMock(StorageInterface::class);
        $serviceFactory = $this->createMock(ServiceFactoryInterface::class);
        $pathFinder = $this->createMock(PathFinderInterface::class);
        $subject = new MigrateCommand(
            $groups,
            $matrix,
            $versionStorage,
            $serviceFactory,
            $pathFinder
        );

        $output = $this->createMock(OutputInterface::class);
        $input = $this->createMock(InputInterface::class);

        $input->expects(static::once())
            ->method('isSetFlag')
            ->with('latest')
            ->willReturnOnConsecutiveCalls(false);

        $this->expectException(MisconfiguredParameterException::class);

        $subject->__invoke(
            $input,
            $output
        );
    }
}
