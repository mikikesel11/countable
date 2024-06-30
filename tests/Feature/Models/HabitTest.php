<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Livewire\Volt\Volt;

class HabitTest extends TestCase
{
    /**
     *  Feature test for the Habits List page
     */
    public function test_list(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->view('habits');

        $response->assertSee('No Habits found. Please create one below!');
        $response->assertSee('Create a New Habit:');
    }

    public function test_create(): void
    {
        $user = User::factory()->create();
        Volt::actingAs($user)
            ->test('habits.create')
            ->assertSee('Create a New Habit:')
            ->set('name', "Workout")
            ->set('type', "CHECK")
            ->call('store')
            ->assertDispatched('habit-created');
    }

}
