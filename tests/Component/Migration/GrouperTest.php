<?php

namespace Ulrack\MigrationExtension\Tests\Component\Migration;

use PHPUnit\Framework\TestCase;
use Ulrack\MigrationExtension\Component\Migration\Grouper;

/**
 * @coversDefaultClass \Ulrack\MigrationExtension\Component\Migration\Grouper
 */
class GrouperTest extends TestCase
{
    /**
     * @covers ::__invoke
     * @covers ::sortMigrationConfiguration
     *
     * @return void
     */
    public function testInvoke(): void
    {
        $subject = new Grouper();

        $migrations = [
            [
                'pool' => 'bar',
                'version' => '1.0.1'
            ],
            [
                'pool' => 'foo',
                'version' => '1.0.0'
            ],
            [
                'pool' => 'foo',
                'version' => '1.0.1'
            ],
            [
                'pool' => 'bar',
                'version' => '1.0.0'
            ]
        ];

        $this->assertEquals(
            [
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
                        'version' => '1.0.1'
                    ]
                ]
            ],
            $subject->__invoke($migrations)
        );
    }
}
