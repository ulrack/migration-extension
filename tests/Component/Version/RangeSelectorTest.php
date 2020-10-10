<?php

namespace Ulrack\MigrationExtension\Tests\Component\Version;

use PHPUnit\Framework\TestCase;
use Ulrack\MigrationExtension\Component\Version\RangeSelector;

/**
 * @coversDefaultClass \Ulrack\MigrationExtension\Component\Version\RangeSelector
 */
class RangeSelectorTest extends TestCase
{
    /**
     * @covers ::__invoke
     *
     * @return void
     */
    public function testInvoke(): void
    {
        $subject = new RangeSelector();

        $versions = ['0.1.0', '1.0.0', '1.0.1', '1.1.0', '2.0.0', '2.0.1'];
        $lowest = '1.0.0';
        $highest = '2.0.0';
        $upgrade = false;

        $this->assertEquals(
            ['1.0.1', '1.1.0', '2.0.0'],
            array_values(
                $subject->__invoke(
                    $versions,
                    $lowest,
                    $highest,
                    $upgrade
                )
            )
        );
    }

    /**
     * @covers ::__invoke
     *
     * @return void
     */
    public function testInvokeUpgrade(): void
    {
        $subject = new RangeSelector();

        $versions = ['0.1.0', '1.0.0', '1.0.1', '1.1.0', '2.0.0', '2.0.1'];
        $lowest = '1.0.0';
        $highest = '2.0.0';
        $upgrade = true;

        $this->assertEquals(
            ['1.0.1', '1.1.0', '2.0.0'],
            array_values(
                $subject->__invoke(
                    $versions,
                    $lowest,
                    $highest,
                    $upgrade
                )
            )
        );
    }
}
