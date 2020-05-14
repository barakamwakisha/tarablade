<?php

namespace Mwakisha\Tarablade\Tests\Unit;

use Mwakisha\Tarablade\Tarablade;
use Orchestra\Testbench\TestCase;

class TarabladeTest extends TestCase
{

    /** @test */
    public function tarablade_can_initialize_properly()
    {
       $tarablade = Tarablade::getInstance();
       $this->assertNotNull($tarablade->applicationContext);
       $this->assertNotNull($tarablade->pathUtils);
       $this->assertNotNull($tarablade->fsUtils);
    }
}
