<?php

namespace Alareqi\FilamentAppVersionManager\Testing;

use Livewire\Features\SupportTesting\Testable;

class TestsFilamentAppVersionManager extends Testable
{
    public function assertCanSeeAppVersionManager(): static
    {
        return $this->assertSee('App Versions');
    }
}
