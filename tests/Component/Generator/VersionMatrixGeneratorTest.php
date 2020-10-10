<?php

namespace Ulrack\MigrationExtension\Tests\Component\Generator;

use PHPUnit\Framework\TestCase;
use Ulrack\MigrationExtension\Component\Generator\VersionMatrixGenerator;
use Ulrack\MigrationExtension\Exception\Matrix\MissingDependencyException;
use Ulrack\MigrationExtension\Exception\Matrix\UnresolvedDependencyException;

/**
 * @coversDefaultClass \Ulrack\MigrationExtension\Component\Generator\VersionMatrixGenerator
 * @covers \Ulrack\MigrationExtension\Exception\Matrix\MissingDependencyException
 * @covers \Ulrack\MigrationExtension\Exception\Matrix\UnresolvedDependencyException
 */
class VersionMatrixGeneratorTest extends TestCase
{
    /**
     * @covers ::__invoke
     *
     * @return void
     */
    public function testInvoke(): void
    {
        $subject = new VersionMatrixGenerator();

        $groupedMigrations = [
            'foo' => [
                '1.0.0' => [
                    'pool' => 'foo',
                    'version' => '1.0.0'
                ],
                '1.0.1' => [
                    'pool' => 'foo',
                    'version' => '1.0.1'
                ]
            ],
            'bar' => [
                '1.0.0' => [
                    'pool' => 'bar',
                    'version' => '1.0.0'
                ],
                '1.0.1' => [
                    'pool' => 'bar',
                    'version' => '1.0.1',
                    'dependant' => [
                        [
                            'pool' => 'foo',
                            'version' => '1.0.1'
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals(
            [
                ['foo' => '0.0.0', 'bar' => '0.0.0'],
                ['foo' => '1.0.0', 'bar' => '1.0.0'],
                ['foo' => '1.0.1', 'bar' => '1.0.0'],
                ['foo' => '1.0.1', 'bar' => '1.0.1']
            ],
            $subject->__invoke($groupedMigrations)
        );
    }

    /**
     * @covers ::__invoke
     *
     * @return void
     */
    public function testInvokeUnresolvedDependency(): void
    {
        $subject = new VersionMatrixGenerator();

        $groupedMigrations = [
            'foo' => [
                '1.0.0' => [
                    'pool' => 'foo',
                    'version' => '1.0.0'
                ],
                '1.0.1' => [
                    'pool' => 'foo',
                    'version' => '1.0.1'
                ]
            ],
            'bar' => [
                '1.0.0' => [
                    'pool' => 'bar',
                    'version' => '1.0.0'
                ],
                '1.0.1' => [
                    'pool' => 'bar',
                    'version' => '1.0.1',
                    'dependant' => [
                        [
                            'pool' => 'foo',
                            'version' => '2.0.0'
                        ]
                    ]
                ]
            ]
        ];

        $this->expectException(UnresolvedDependencyException::class);
        $subject->__invoke($groupedMigrations);
    }

    /**
     * @covers ::__invoke
     *
     * @return void
     */
    public function testInvokeMissingDependency(): void
    {
        $subject = new VersionMatrixGenerator();

        $groupedMigrations = [
            'foo' => [
                '1.0.0' => [
                    'pool' => 'foo',
                    'version' => '1.0.0'
                ],
                '1.0.1' => [
                    'pool' => 'foo',
                    'version' => '1.0.1'
                ]
            ],
            'bar' => [
                '1.0.0' => [
                    'pool' => 'bar',
                    'version' => '1.0.0'
                ],
                '1.0.1' => [
                    'pool' => 'bar',
                    'version' => '1.0.1',
                    'dependant' => [
                        [
                            'pool' => 'baz',
                            'version' => '1.0.0'
                        ]
                    ]
                ]
            ]
        ];

        $this->expectException(MissingDependencyException::class);
        $subject->__invoke($groupedMigrations);
    }
}
