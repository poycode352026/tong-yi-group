<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    public function test_home_renders_the_vue_page_through_inertia(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Home'));
    }

    public function test_application_connects_to_postgresql(): void
    {
        $this->assertSame('pgsql', DB::connection()->getDriverName());
        $this->assertSame(1, DB::selectOne('SELECT 1 AS connected')->connected);
    }
}
