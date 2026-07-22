<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // _settingsCache() (app/helpers.php) usa una variable static de PHP que
        // sobrevive entre tests dentro del mismo proceso, aunque RefreshDatabase
        // reinicie la BD — sin este reset, un test que escribe settings (vía
        // updateSettings()) contamina lo que setting() devuelve en el siguiente
        // test del mismo proceso.
        _settingsCache(null, reset: true);
    }
}
