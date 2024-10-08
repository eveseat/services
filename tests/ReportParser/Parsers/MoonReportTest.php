<?php

namespace Seat\Tests\Services\ReportParser\Parsers;

use PHPUnit\Framework\TestCase;
use Seat\Services\ReportParser\Elements\Group;
use Seat\Services\ReportParser\Exceptions\EmptyReportException;
use Seat\Services\ReportParser\Exceptions\InvalidReportGroupException;
use Seat\Services\ReportParser\Exceptions\MissingReportGroupException;
use Seat\Services\ReportParser\Exceptions\MissingReportHeaderException;
use Seat\Services\ReportParser\Parsers\MoonReport;

/**
 * Class MoonReportTest.
 */
class MoonReportTest extends TestCase
{
    public static function correctFormatProvider(): array
    {
        return [
            'from EVE'     => ['/../../artifacts/moon_report.txt'],
            'from Excel'   => ['/../../artifacts/moon_report_excel.txt'],
            'mixed inputs' => ['/../../artifacts/moon_report_mixed.txt']
        ];
    }

    public static function malformedElementsProvider(): array
    {
        return [
            'no elements' => ['/../../artifacts/moon_report_without_elements.txt']
        ];
    }

    public static function malformedGroupsProvider(): array
    {
        return [
            'no groups' => ['/../../artifacts/moon_report_without_groups.txt']
        ];
    }

    public static function malformedHeaderProvider(): array
    {
        return [
            'no header' => ['/../../artifacts/moon_report_without_header.txt']
        ];
    }

    /**
     * @dataProvider correctFormatProvider
     */
    public function testGetElements(string $artifact_path)
    {
        $content = file_get_contents(__DIR__ . $artifact_path);

        $report = new MoonReport();
        $report->parse($content);

        $this->assertTrue($report->getElements() === []);
    }

    public function testIsEmpty()
    {
        $report = new MoonReport();

        $this->assertTrue($report->isEmpty());

        $report->parse('');
        $this->assertTrue($report->isEmpty());
    }

    public function testEmptyReportException()
    {
        $report = new MoonReport();

        $this->expectException(EmptyReportException::class);
        $report->validate();

        $report->parse('');

        $this->expectException(EmptyReportException::class);
        $report->validate();
    }

    /**
     * @dataProvider malformedHeaderProvider
     */
    public function testMissingReportHeaderException(string $artifact_path)
    {
        $content = file_get_contents(__DIR__ . $artifact_path);

        $report = new MoonReport();
        $report->parse($content);

        $this->expectException(MissingReportHeaderException::class);
        $report->validate();
    }

    /**
     * @dataProvider malformedGroupsProvider
     */
    public function testMissingReportGroupException(string $artifact_path)
    {
        $content = file_get_contents(__DIR__ . $artifact_path);

        $report = new MoonReport();
        $report->parse($content);

        $this->expectException(MissingReportGroupException::class);
        $report->validate();
    }

    /**
     * @dataProvider malformedElementsProvider
     */
    public function testInvalidReportGroupException(string $artifact_path)
    {
        $content = file_get_contents(__DIR__ . $artifact_path);

        $report = new MoonReport();
        $report->parse($content);

        $this->expectException(InvalidReportGroupException::class);
        $report->validate();
    }

    /**
     * @dataProvider correctFormatProvider
     */
    public function testParse(string $artifact_path)
    {
        $content = file_get_contents(__DIR__ . $artifact_path);

        $report = new MoonReport();
        $report->parse($content);
        $report->validate();

        $groups = $report->getGroups();

        $this->assertArrayAreEqual([
            'Moon',
            'Moon Product',
            'Quantity',
            'Ore TypeID',
            'SolarSystemID',
            'PlanetID',
            'MoonID',
        ], $report->getHeader()->fields());

        $this->assertEquals('OP9L-F II - Moon 10', $groups[0]->getName());

        $this->assertArrayAreEqual([
            'moon'          => '',
            'moonProduct'   => 'Glossy Scordite',
            'quantity'      => '0.300030559301',
            'oreTypeID'     => '46687',
            'solarSystemID' => '30002173',
            'planetID'      => '40138526',
            'moonID'        => '40138527',
        ], $groups[0]->getElements()[0]->fields());

        $this->assertArrayAreEqual([
            'moon'          => '',
            'moonProduct'   => 'Immaculate Jaspet',
            'quantity'      => '0.328855156898',
            'oreTypeID'     => '46682',
            'solarSystemID' => '30002173',
            'planetID'      => '40138526',
            'moonID'        => '40138527',
        ], $groups[0]->getElements()[1]->fields());

        $this->assertArrayAreEqual([
            'moon'          => '',
            'moonProduct'   => 'Pellucid Crokite',
            'quantity'      => '0.287893354893',
            'oreTypeID'     => '46677',
            'solarSystemID' => '30002173',
            'planetID'      => '40138526',
            'moonID'        => '40138527',
        ], $groups[0]->getElements()[2]->fields());

        $this->assertArrayAreEqual([
            'moon'          => '',
            'moonProduct'   => 'Sylvite',
            'quantity'      => '0.083220936358',
            'oreTypeID'     => '45491',
            'solarSystemID' => '30002173',
            'planetID'      => '40138526',
            'moonID'        => '40138527',
        ], $groups[0]->getElements()[3]->fields());

        $this->assertEquals('OP9L-F VII - Moon 8', $groups[1]->getName());

        $this->assertArrayAreEqual([
            'moon'          => '',
            'moonProduct'   => 'Dazzling Spodumain',
            'quantity'      => '0.397311687469',
            'oreTypeID'     => '46688',
            'solarSystemID' => '30002173',
            'planetID'      => '40138538',
            'moonID'        => '40138546',
        ], $groups[1]->getElements()[0]->fields());

        $this->assertArrayAreEqual([
            'moon'          => '',
            'moonProduct'   => 'Immaculate Jaspet',
            'quantity'      => '0.412641495466',
            'oreTypeID'     => '46682',
            'solarSystemID' => '30002173',
            'planetID'      => '40138538',
            'moonID'        => '40138546',
        ], $groups[1]->getElements()[1]->fields());

        $this->assertArrayAreEqual([
            'moon'          => '',
            'moonProduct'   => 'Sylvite',
            'quantity'      => '0.190046817064',
            'oreTypeID'     => '45491',
            'solarSystemID' => '30002173',
            'planetID'      => '40138538',
            'moonID'        => '40138546',
        ], $groups[1]->getElements()[2]->fields());
    }

    /**
     * @dataProvider correctFormatProvider
     */
    public function testHasGroups(string $artifact_path)
    {
        $content = file_get_contents(__DIR__ . $artifact_path);

        $report = new MoonReport();
        $report->parse($content);

        $this->assertTrue($report->hasGroups());
    }

    /**
     * @dataProvider correctFormatProvider
     */
    public function testGetHeader(string $artifact_path)
    {
        $content = file_get_contents(__DIR__ . $artifact_path);

        $report = new MoonReport();
        $report->parse($content);

        $this->assertArrayAreEqual([
            'Moon',
            'Moon Product',
            'Quantity',
            'Ore TypeID',
            'SolarSystemID',
            'PlanetID',
            'MoonID',
        ], $report->getHeader()->fields());
    }

    /**
     * @dataProvider correctFormatProvider
     */
    public function testHasHeader(string $artifact_path)
    {
        $content = file_get_contents(__DIR__ . $artifact_path);

        $report = new MoonReport();
        $report->parse($content);

        $this->assertTrue($report->hasHeader());
    }

    /**
     * @dataProvider correctFormatProvider
     */
    public function testGetGroups(string $artifact_path)
    {
        $content = file_get_contents(__DIR__ . $artifact_path);

        $report = new MoonReport();
        $report->parse($content);

        $this->assertEquals(count($report->getGroups()), 2);
        $this->assertContainsOnlyInstancesOf(Group::class, $report->getGroups());
    }

    /**
     * @dataProvider correctFormatProvider
     */
    public function testHasElements(string $artifact_path)
    {
        $content = file_get_contents(__DIR__ . $artifact_path);

        $report = new MoonReport();
        $report->parse($content);

        $this->assertFalse($report->hasElements());
    }

    /**
     * @param array $expected
     * @param array $actual
     * @param string $message
     */
    public static function assertArrayAreEqual(array $expected, array $actual, string $message = ''): void
    {
        static::assertTrue(array_diff_assoc($expected, $actual) === [], $message);
    }
}
