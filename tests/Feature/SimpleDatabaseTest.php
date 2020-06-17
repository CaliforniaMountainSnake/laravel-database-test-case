<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use Tests\DatabaseTestCase;

class SimpleDatabaseTest extends DatabaseTestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testGetUsers(): void
    {
        $users = DB::table('users')->get()->toArray();
        $this->logger->debug('Users:', $users);
        $this->assertTrue(count($users) > 0);
    }
}
