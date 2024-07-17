<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use App\Models\Habit;
use App\Models\Count;

new class extends Component {
    public Habit $habit;

    #[Validate('integer|min:0', message: "Count must be an Integer")]
    public $current_count = 0;

    #[Validate('required|date', message: "Tracked for Date required")]
    public $tracked_for_date;

    public $check;
    public $finalized;

    public function mount(Habit $habit)
    {
        $this->habit = $habit;
        $this->tracked_for_date = today()->format('Y-m-d');
    }

    public function store() 
    {
        if($this->habit->type === "CHECK") {
            $this->current_count = 1;
            $this->finalized = now();
        } elseif($this->habit->type === "NUMBER") {
            $this->finalized = null;
        }
        $validated = $this->validate();
        $validated['habit_id'] = $this->habit->id;
        $validated['habit_name'] = $this->habit->name;
        unset($validated['check']);
        $checkCount = Count::where('user_id', auth()->user()->id)
            ->where('habit_id', $this->habit->id)
            ->where('tracked_for_date', $this->tracked_for_date)
            ->latest()
            ->first();
        if($checkCount)
        {
            $this->authorize('update', $checkCount);
            $update = array();
            $update['current_count'] = $validated['current_count'] + $checkCount->current_count;
            $update['finalized'] = $this->finalized;
            $checkCount->update($update);
            $this->current_count = 0;
            $this->tracked_for_date = today()->format('Y-m-d');
            $this->check = false;
            $this->dispatch('count-created');
        } else {
            auth()->user()->counts()->create($validated);
            $this->current_count = 0;
            $this->tracked_for_date = today()->format('Y-m-d');
            $this->check = false;
            $this->dispatch('count-created');
        }
    }

}; ?>

<div class="flex flex-col mt-6 bg-white shadow-sm md:justify-between md:items-center rounded-lg divide-y dark:bg-gray-700 dark:text-white">
    <div class="flex py-2 mx-auto md:justify-between md:items-center my-auto">
        <h3>Create a New {{$habit->name}} Count:</h3>
    </div>
    <div class="flex flex-col justify-between items-center space-y-2 mx-auto my-auto">
        <form wire:submit="store"  class="flex flex-col justify-between items-center space-y-2 mx-auto my-auto"> 
            @csrf
            @if($this->habit->type === "NUMBER")
            <div class="flex">
                <input type="number" wire:model.number="current_count" class="dark:bg-gray-800 dark:text-white" aria-label="Current Count" id="current_count" name="current_count" min="0"/>
                <x-input-error :messages="$errors->get('current_count')" class="my-2" />
            </div>
            @elseif($this->habit->type === "CHECK")
            <div class="flex space-x-2">
                <x-input-label for="check" class="dark:text-white">Completed for Today</x-input-label>
                <input type="checkbox" id="check" name="check" wire:model.boolean="check" wire.confirm="Are you sure you want to consider this Count Completed?" class="appearance-none mt-1 checked:bg-violet-800 dark:bg-gray-800 dark:text-white" />
                <x-input-error :messages="$errors->get('current_count')" class="my-2" />
            </div>
            @endif
            <div class="flex">
            <input type="date" class="dark:bg-gray-800 dark:text-white" aria-label="Tracked For Date" wire:model="tracked_for_date" id="tracked_for_date" name="tracked_for_date" />
            <x-input-error :messages="$errors->get('tracked_for_date')" class="my-2" />
            </div>
            <div class="flex-auto flex-col justify-between items-center">
                <x-primary-button type="submit" class="bg-violet-600 dark:bg-violet-800 dark:text-white" aria-label="Submit">Submit</x-primary-button>
            </div>
            @env('local')
            <p>{{print_r($errors)}}</p>
            @endenv
        </form> 
    </div>
</div>
