<?php

namespace CaliforniaMountainSnake\LaravelDatabaseTestCase\Utils;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesLaravelApplication
{
    /**
     * Get the absolute path to the /bootstrap/app.php.
     * @return string
     */
    abstract public function getAppFilePath(): string;

    /**
     * Creates the application.
     *
     * @return Application
     */
    public function createApplication(): Application
    {
        $app = require $this->getAppFilePath();

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
