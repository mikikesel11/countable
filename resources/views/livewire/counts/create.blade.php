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

<div class="mt-6 bg-white shadow-sm rounded-lg divide-y dark:bg-gray-700 dark:text-white">
    <div class="flex-col py-2 basis-1/2 mx-auto my-auto">
        <h3 class="basis-1/2 text-lg mx-auto px-6">Create a New {{$habit->name}} Count:</h3>
    </div>
    <div class="flex-col py-2 basis-1/2 mx-auto my-auto">
        <form class="px-6 my-auto flex-col space-x-4" wire:submit="store"> 
            @if($this->habit->type === "NUMBER")
            <input type="number" wire:model.number="current_count" class="my-4 dark:bg-gray-800 dark:text-white" aria-label="Current Count" id="current_count" name="current_count" min="0"/>
            @elseif($this->habit->type === "CHECK")
            <input type="checkbox" id="check" name="check" wire:model.boolean="check" wire.confirm="Are you sure you want to consider this Count Completed?" class="appearance-none mt-1 checked:bg-violet-800 dark:bg-gray-800 dark:text-white" />
            <label for="check" class="dark:text-white">Completed for Today</label>
            @endif
            <x-input-error :messages="$errors->get('current_count')" class="my-2" />
            <input type="date" class="my-4 dark:bg-gray-800 dark:text-white" aria-label="Tracked For Date" wire:model="tracked_for_date" id="tracked_for_date" name="tracked_for_date" />
            <x-input-error :messages="$errors->get('tracked_for_date')" class="my-2" />
            <x-primary-button type="submit" class="my-4 bg-violet-200 dark:bg-violet-800 dark:text-white" aria-label="Submit">Submit</x-primary-button>
            @env('local')
            <p>{{print_r($errors)}}</p>
            @endenv
        </form> 
    </div>
</div>
