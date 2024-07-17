<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Habit;
use App\Models\Count;
use Livewire\Volt\Volt;
use Livewire\Livewire;

class HabitTest extends TestCase
{
    use RefreshDatabase;
    /**
     *  Feature test for the Habits List page
     */
    public function test_empty_list(): void
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

    public function test_edit(): void
    {
        $user = User::factory()->create();
        $habit = Habit::factory()->create(['user_id' => $user->id, 'active' => 1]);
        Volt::actingAs($user)
            ->test('habits.edit', ['habit' => $habit])
            ->assertSee('Edit Habit:')
            ->set('name', "Workout before Noon")
            ->call('update')
            ->assertDispatched('habit-updated');
    }

    public function test_history(): void
    {
        $user = User::factory()->create();
        $habit = Habit::factory()->create(['user_id' => $user->id, 'active' => 1]);
        $count = Count::factory()->create([
            'user_id' => $user->id, 
            'habit_id' => $habit->id, 
            'habit_name' => $habit->name,
        ]);
        Livewire::actingAs($user)
            ->test('counts.list', ['habit' => $habit])
            ->assertSee("$habit->name Counts")
            ->assertCount('counts', 1);
    }
}
