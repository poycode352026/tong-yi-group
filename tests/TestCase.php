<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use LogicException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        // Dijalankan sebelum RefreshDatabase boleh melakukan migrasi/pembersihan.
        if (! $app->environment('testing')
            || DB::connection()->getDriverName() !== 'pgsql'
            || DB::selectOne('SELECT current_database() AS name')->name !== 'tong_yi_group_test') {
            throw new LogicException('Tes hanya boleh dijalankan pada PostgreSQL tong_yi_group_test.');
        }

        return $app;
    }
}
