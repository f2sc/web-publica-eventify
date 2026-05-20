<?php
namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNavTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_nav_includes_calendario_link(): void
    {
        $this->withSession(['cms_token' => 'test'])
             ->get('/admin/articulos')
             ->assertStatus(200)
             ->assertSee('Calendario');
    }
}
