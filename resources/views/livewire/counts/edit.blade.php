<?php

use Livewire\Volt\Component;
use App\Models\Count;
use App\Models\Habit;
use Livewire\Attributes\Validate;

new class extends Component {
    public Count $count; 
    public Habit $habit;
 
    #[Validate('required|int|min:0', message: 'Count Required')]
    public $current_count;

    #[Validate('required|date', message: 'Tracked for Date Required')]
    public $tracked_for_date;

    public $final = false;
    public $finalized;
    public $check = false;

    public function mount(Habit $habit): void
    {
        $this->habit = $habit;
        $this->current_count = $this->count->current_count;
        $this->tracked_for_date = $this->count->tracked_for_date;
        $this->finalized = $this->count->finalized;
        if($this->count->finalized) 
        {
            $this->final = true;
        }
        if($this->habit->type === "CHECK") {
            if($this->current_count > 0) {
                $this->check = true;
            }
        }
    }
 
    public function update(): void
    {
        $this->authorize('update', $this->count);
        if($this->final) {
            $this->finalized = now();
        } else {
            $this->finalized = null;
        }
        if($this->check) {
            $this->current_count = 1;
        }
        $validated = $this->validate();
        $validated['habit_id'] = $this->habit->id;
        $validated['habit_name'] = $this->habit->name;

        $this->count->updateOrFail($validated);
        $this->dispatch('count-updated');
    }

    public function cancel(): void
    {
        $this->dispatch('count-edit-canceled');
    }  
}; ?>

<div class="my-6 bg-white shadow-sm rounded-lg divide-y dark:bg-gray-700 dark:text-white md:justify-between md:items-center">
    <div class="flex flex-col basis-1/2 space-y-2 mx-auto my-auto md:justify-between md:items-center">
        <form class="flex flex-col" wire:submit="update" wire:key="{{$count->id}}">
            @csrf
            <div>
                <h3 class="basis-1/2 text-lg ">Edit Count:</h3>
            </div>
            <div>
                @if($this->habit->type === 'NUMBER')
                <input type="number" wire:model.number="current_count" class="dark:bg-gray-800 dark:text-white" aria-label="Current Count" id="current_count" name="current_count"/>
                @elseIf($this->habit->type === 'CHECK')
                <input type="checkbox" id="check" name="check" wire:model.boolean="check" class="appearance-none mt-1 checked:bg-violet-800 dark:bg-gray-800 dark:text-white" />
                <label for="check" class="dark:text-white px-2">Complete</label>
                @endif
                <x-input-error :messages="$errors->get('current_count')" class="mx-2 dark:bg-gray-800 dark:text-white" />
            </div>
            <div>
                <input type="date" class="my-4 dark:bg-gray-800 dark:text-white" aria-label="Tracked For Date" wire:model="tracked_for_date" id="tracked_for_date" name="tracked_for_date" />
                <x-input-error :messages="$errors->get('tracked_for_date')" class="mx-2 dark:bg-gray-800 dark:text-white" />
            </div>
            <div>
                <input type="checkbox" wire:model.boolean="final" id="final" name="final" class="appearance-none mt-1 checked:bg-violet-800 dark:bg-gray-800 dark:text-white" />
                <label for="final" class="px-2 dark:text-white">Finalize</label>
                <p class="dark:text-white">This will complete the count for the Tracking for Date.</p>
            </div>
            <div class="flex space-x-4">
                <x-primary-button class="btn bg-violet-600 dark:bg-violet-800 dark:text-white hover:bg-violet-800">{{ __('Update') }}</x-primary-button>
                <x-secondary-button class="btn" wire:click.prevent="cancel">Cancel</x-secondary-button>
            </div>
        </form>
    </div>
</div>
