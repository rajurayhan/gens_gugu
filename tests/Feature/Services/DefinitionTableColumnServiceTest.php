<?php

namespace Tests\Feature\Services;

use App\Services\DefinitionTableColumnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DefinitionTableColumnServiceTest extends TestCase
{
    /**
     * BIGINT
     *
     * @return void
     */
    public function testGetColumnSizeForBigInt()
    {
        $DefinitionTableColumnService = new DefinitionTableColumnService();
        $byte = $DefinitionTableColumnService->getTableColumnByte('bigint', []);

        $this->assertEquals(8, $byte);
    }

    /**
     * DATE
     *
     * @return void
     */
    public function testGetColumnSizeForDate()
    {
        $DefinitionTableColumnService = new DefinitionTableColumnService();
        $byte = $DefinitionTableColumnService->getTableColumnByte('date', []);

        $this->assertEquals(3, $byte);
    }

    /**
     * DATETIME
     *
     * @return void
     */
    public function testGetColumnSizeForDatetime()
    {
        $DefinitionTableColumnService = new DefinitionTableColumnService();
        $byte = $DefinitionTableColumnService->getTableColumnByte('datetime', []);

        $this->assertEquals(8, $byte);
    }

    /**
     * DECIMAL
     * Integer: 10
     * Fractional: 2
     *
     * @return void
     */
    public function testGetColumnSizeForDecimal_10_2()
    {
        $DefinitionTableColumnService = new DefinitionTableColumnService();
        $byte = $DefinitionTableColumnService->getTableColumnByte('decimal', [
            'maximum_number' => 10,
            'decimal_part' => 2,
        ]);

        // Max値である 30byteを返す
        // $this->assertEquals(5, $byte);
        $this->assertEquals(30, $byte);
    }

    /**
     * DECIMAL
     * Integer: 65 (Max)
     * Fractional: 30 (Max)
     *
     * @return void
     */
    public function testGetColumnSizeForDecimal_65_30()
    {
        $DefinitionTableColumnService = new DefinitionTableColumnService();
        $byte = $DefinitionTableColumnService->getTableColumnByte('decimal', [
            'maximum_number' => 65,
            'decimal_part' => 30,
        ]);

        $this->assertEquals(30, $byte);
    }

    /**
     * VARCHAR
     * Under 255 byte
     *
     * @return void
     */
    public function testGetColumnSizeForDecimal_under255()
    {
        $DefinitionTableColumnService = new DefinitionTableColumnService();
        $byte = $DefinitionTableColumnService->getTableColumnByte('varchar', [
            'length' => 255,
        ]);

        $this->assertEquals(255 * 4 + 1, $byte);
    }

    /**
     * VARCHAR
     * More than 255 byte
     *
     * @return void
     */
    public function testGetColumnSizeForDecimal_moreThan256()
    {
        $DefinitionTableColumnService = new DefinitionTableColumnService();
        $byte = $DefinitionTableColumnService->getTableColumnByte('varchar', [
            'length' => 256,
        ]);

        $this->assertEquals(256 * 4 + 2, $byte);
    }

    /**
     * TIMESTAMP
     *
     * @return void
     */
    public function testGetColumnSizeForTimestamp()
    {
        $DefinitionTableColumnService = new DefinitionTableColumnService();
        $byte = $DefinitionTableColumnService->getTableColumnByte('timestamp', []);

        $this->assertEquals(7, $byte);
    }

    /**
     * OTHER
     *
     * @return void
     */
    public function testGetColumnSizeForOther()
    {
        $DefinitionTableColumnService = new DefinitionTableColumnService();
        $byte = $DefinitionTableColumnService->getTableColumnByte('', []);

        $this->assertEquals(0, $byte);
    }
}
