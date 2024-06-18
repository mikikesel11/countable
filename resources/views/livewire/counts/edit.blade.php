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

    public $final;
    public $finalized;
    public $check = false;
    public $streak;

    public function mount(Habit $habit): void
    {
        $this->habit = $habit;
        $this->current_count = $this->count->current_count;
        $this->tracked_for_date = $this->count->tracked_for_date;
        $this->finalized = null;
        $this->final = false;
        $this->streak = $this->count->streak;
        if($this->habit->type === "CHECK") {
            if($this->current_count > 0) {
                $this->check = true;
            }
        }
    }
 
    public function update(): void
    {
        if($this->count->finalized !== null) {
            $this->dispatch('count-edit-canceled', ['message' => 'Cannot update a finalized count.']);
        }
        $this->authorize('update', $this->count);
        if($this->final) {
            $this->finalized = now();
            if($this->current_count > 0 && !$this->check) {
                $this->habit->current_streak = $this->count->streak + 1;   
            }
        } else {
            $this->finalized = null;
        }
        if($this->check) {
            $this->current_count = 1;
        }
        $validated = $this->validate();
        $validated['habit_id'] = $this->habit->id;
        $validated['habit_name'] = $this->habit->name;
        $validated['current_count'] = $this->current_count;
        $validated['finalized'] = $this->finalized;
        $validated['tracked_for_date'] = $this->tracked_for_date;

        $this->count->updateOrFail($validated);
        $this->dispatch('count-updated');
    }

    public function cancel(): void
    {
        $this->dispatch('count-edit-canceled');
    }  
}; ?>

<div class="my-6 bg-white shadow-sm rounded-lg divide-y dark:bg-gray-700 dark:text-white">
    <div class="flex-col py-2 basis-1/2 mx-auto my-auto">
        <form class="flex flex-col space-y-2" wire:submit="update" wire:key="{{$count->id}}">
            <div class="flex">
                @if($this->habit->type === 'NUMBER')
                <input type="number" wire:model.number="current_count" class="mx-4 py-auto dark:bg-gray-800 dark:text-white" aria-label="Current Count" id="current_count" name="current_count"/>
                @elseIf($this->habit->type === 'CHECK')
                <input type="checkbox" id="check" name="check" wire:model.boolean="check" wire.confirm="Are you sure you want to consider this Count Completed?" class="appearance-none mt-1 checked:bg-violet-800 dark:bg-gray-800 dark:text-white" />
                <label for="check" class="px-2 dark:text-white">Complete</label>
                @endif
                <x-input-error :messages="$errors->get('current_count')" class="mx-2 dark:bg-gray-800 dark:text-white" />
            </div>
            <div class="flex">
                <input type="date" class="my-4 dark:bg-gray-800 dark:text-white" aria-label="Tracked For Date" wire:model="tracked_for_date" id="tracked_for_date" name="tracked_for_date" />
                <x-input-error :messages="$errors->get('tracked_for_date')" class="mx-2 dark:bg-gray-800 dark:text-white" />
            </div>
            <div class="flex">
                <input type="checkbox" wire:model.boolean="final" id="final" name="final" wire.confirm="Are you sure you want to Finalize this Count?" class="mt-1 dark:bg-gray-800 dark:text-white" />
                <label for="final" class="px-2 dark:text-white">Finalize</label>
                <p class="dark:text-white text-sm pt-1">This will complete the count for the Tracking for Date.</p>
            </div>
            <div class="flex space-x-4">
                <x-primary-button class="btn dark:bg-gray-800 dark:text-white">{{ __('Update') }}</x-primary-button>
                <x-secondary-button class="btn" wire:click.prevent="cancel">Cancel</x-secondary-button>
            </div>
        </form>
    </div>
</div>
