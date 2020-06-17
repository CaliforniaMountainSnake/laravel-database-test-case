<?php

namespace CaliforniaMountainSnake\LaravelDatabaseTestCase\Utils;

use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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
     * @return HttpKernelInterface
     */
    public function createApplication()
    {
        $app = require $this->getAppFilePath();

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
