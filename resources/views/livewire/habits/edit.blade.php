<?php

use App\Models\Habit;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component {
    public Habit $habit;

    #[Validate('required|string|max:255')]
    public string $name;

    #[Validate('required')]
    public $type;

    public bool $active;

    public function mount(Habit $habit): void
    {
        $this->habit = $habit;
        $this->name = $this->habit->name;
        $this->type = $this->habit->type;
        $this->active = $this->habit->active;
    }
 
    public function update(): void
    {
        $this->authorize('update', $this->habit);
 
        $validated = $this->validate();
        $validated['active'] = $this->active ? 1:0;
        $validated['user_id'] = auth()->user()->id;

        $this->habit->updateOrFail($validated);
 
        $this->dispatch('habit-updated');
    }
 
    public function cancel(): void
    {
        $this->dispatch('habit-edit-canceled');
    }  
}; ?>

<div class="basis-1/4 mx-auto p-6 bg-white shadow-sm rounded-lg divide-y dark:bg-gray-700 dark:text-white">
    <form wire:submit="update"> 
    @csrf
    <div class="flex flex-col">
        <div class="flex-col justify-between items-center mx-auto my-auto">
            <div class="flex-col py-2 basis-1/2 mx-auto my-auto">
                <h3 class="basis-1/2 text-lg ">Edit Habit:</h3>
            </div>
            <div class="flex-col py-2 basis-1/2 mx-auto my-auto">
                <label for="name" class=" ">Habit Name:</label>
                <input wire:model="name" id="name" type="text" class="bg-white shadow-sm rounded-lg divide-y dark:bg-gray-700 dark:text-white" aria-label="Habit Name" placeholder="Habit Name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            @if($this->type === "NUMBER")
            <div class="flex-col py-2 basis-1/2 mx-auto my-auto">
                <div class="basis-1/2 mx-auto my-auto">
                    <input type="radio" class="dark:bg-gray-800 checked:bg-violet-800" aria-label="Checkbox Count" id="CHECK" name="type" wire:model="type" value="Check" aria-disabled="disabled" disabled/>
                    <label for="CHECK" class="mt-6">Checkbox style counts</label>
                </div>
                <div class="basis-1/2 mx-auto my-auto">
                    <input type="radio" class="dark:bg-gray-800 checked:bg-violet-800" aria-label="Numeric Count" id="NUMBER" name="type" wire:model="type" value="Number" aria-disabled="disabled" disabled checked />
                    <label for="NUMBER" class="mt-6">Numeric style counts</label>
                </div>
                <x-input-error :messages="$errors->get('type')" class="mt-2" />
            </div>
            @elseif($this->type === "CHECK")
            <div class="flex-col py-2 basis-1/2 mx-auto my-auto">
                <div class="basis-1/2 mx-auto my-auto">
                    <input type="radio" class="dark:bg-gray-800 checked:bg-violet-800" aria-label="Checkbox Count" id="CHECK" name="type" wire:model="type" value="CHECK" aria-disabled="disabled" disabled checked />
                    <label for="CHECK" class="mt-6">Checkbox style counts</label>
                </div>
                <div class="basis-1/2 mx-auto my-auto">
                    <input type="radio" class="dark:bg-gray-800 checked:bg-violet-800" aria-label="Numeric Count" id="NUMBER" name="type" wire:model="type" value="NUMBER" aria-disabled="disabled" disabled />
                    <label for="NUMBER" class="mt-6">Numeric style counts</label>
                </div>
                <x-input-error :messages="$errors->get('type')" class="mt-2" />
            </div>
            @endif
            <div class="flex-col py-2 basis-1/2 mx-auto my-auto">
                <input type="checkbox" wire:model.boolean="active" id="active" name="active" class="mt-1 dark:bg-gray-800 checked:bg-violet-800 dark:text-white" />
                <label for="active" class="px-2 dark:text-white">Active</label>
                <x-input-error :messages="$errors->get('active')" class="mt-2" />
            </div>
            <x-primary-button class="py-2 mt-4 bg-violet-600 dark:bg-violet-800 dark:text-white" aria-label="Submit">Submit</x-primary-button> 
            <x-secondary-button class="btn" wire:click.prevent="cancel">Cancel</x-secondary-button>
        </div>
    </div>
    @env('local')
        <p>{{print_r($errors)}}</p>
    @endenv
    </form> 
</div>

