<?php

namespace Ulrack\MigrationExtension\Tests\Component\Migration;

use PHPUnit\Framework\TestCase;
use GrizzIt\Storage\Component\ObjectStorage;
use Ulrack\MigrationExtension\Component\Migration\PathFinder;
use Ulrack\MigrationExtension\Component\Version\RangeSelector;
use Ulrack\MigrationExtension\Exception\Migration\UndefinedVersionException;

/**
 * @coversDefaultClass \Ulrack\MigrationExtension\Component\Migration\PathFinder
 * @covers \Ulrack\MigrationExtension\Exception\Migration\UndefinedVersionException
 */
class PathFinderTest extends TestCase
{
    /**
     * @covers ::__invoke
     * @covers ::getMigrationPath
     * @covers ::getPreviousVersion
     * @covers ::resolveMigrationPath
     * @covers ::getExternalDependencies
     * @covers ::getDependencies
     * @covers ::getRestoreConfig
     * @covers ::__construct
     *
     * @param array $versionConfig
     * @param array $storageContent
     * @param array $groups
     * @param array $restoreConfig
     * @param array $result
     * @param bool $throws
     *
     * @return void
     *
     * @dataProvider invocationProvider
     */
    public function testInvoke(
        array $versionConfig,
        array $storageContent,
        array $groups,
        array $restoreConfig,
        array $result,
        bool $throws
    ): void {
        $rangeSelector = new RangeSelector();
        $subject = new PathFinder($rangeSelector);
        $versionStorage = new ObjectStorage($storageContent);

        if ($throws) {
            $this->expectException(UndefinedVersionException::class);
            $subject->__invoke(
                $versionConfig,
                $versionStorage,
                $groups
            );
        } else {
            $this->assertEquals(
                $result,
                $subject->__invoke(
                    $versionConfig,
                    $versionStorage,
                    $groups
                )
            );

            $this->assertEquals($restoreConfig, $subject->getRestoreConfig());
        }
    }

    /**
     * Data provider for testing.
     *
     * @return array
     */
    public function invocationProvider(): array
    {
        return [
            [
                ['foo' => '1.0.0', 'bar' => '1.0.0'],
                ['foo' => '0.0.1'],
                [
                    'foo' => [
                        '0.0.1' => [],
                        '1.0.0' => [
                            'dependant' => [
                                [
                                    'pool' => 'bar',
                                    'version' => '1.0.0'
                                ]
                            ]
                        ]
                    ],
                    'bar' => [
                        '1.0.0' => []
                    ]
                ],
                ['foo' => '0.0.1', 'bar' => '0.0.0'],
                [
                    [
                        'pool' => 'bar',
                        'version' => '1.0.0',
                        'to_version' => '1.0.0',
                        'method' => 'apply'
                    ],
                    [
                        'pool' => 'foo',
                        'version' => '1.0.0',
                        'to_version' => '1.0.0',
                        'method' => 'apply'
                    ]
                ],
                false
            ],
            [
                ['bar' => '0.0.0'],
                ['foo' => '1.0.0', 'bar' => '1.0.0'],
                [
                    'foo' => [
                        '0.0.1' => [
                            'version' => '0.0.1'
                        ],
                        '1.0.0' => [
                            'version' => '1.0.0',
                            'dependant' => [
                                [
                                    'pool' => 'bar',
                                    'version' => '1.0.0'
                                ]
                            ]
                        ]
                    ],
                    'bar' => [
                        '1.0.0' => [
                            'version' => '1.0.0'
                        ]
                    ]
                ],
                ['foo' => '1.0.0', 'bar' => '1.0.0'],
                [
                    [
                        'pool' => 'foo',
                        'version' => '1.0.0',
                        'to_version' => '0.0.1',
                        'method' => 'revert'
                    ],
                    [
                        'pool' => 'bar',
                        'version' => '1.0.0',
                        'to_version' => '0.0.0',
                        'method' => 'revert'
                    ]
                ],
                false
            ],
            [
                [ 'foo' => '1.0.0'],
                [],
                ['foo' => ['0.0.1' => []]],
                [],
                [],
                true
            ]
        ];
    }
}
