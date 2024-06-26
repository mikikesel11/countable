<?php

use Livewire\Volt\Component;
use Livewire\Attributes\On;
use App\Models\Habit;
use Illuminate\Database\Eloquent\Collection;

new class extends Component {
    public Collection $habits;

    public ?Habit $editing = null;

    public function mount(): void
    {
        $this->getHabits();
    }

    #[On('habit-created')]
    public function getHabits(): void
    {
        $this->habits = Habit::where('user_id', auth()->user()->id)
            ->where('active', 1)
            ->latest()
            ->get();
    }

    public function edit(Habit $habit)
    {
        $this->editing = $habit;

        $this->getHabits();
    }

    #[On('habit-edit-canceled')]
    #[On('habit-updated')] 
    public function disableEditing(): void
    {
        $this->editing = null;
 
        $this->getHabits();
    } 

    public function delete(Habit $habit): void
    {
        $this->authorize('delete', $habit);
 
        $habit->delete();
 
        $this->getHabits();
    } 

}; ?>

<div class="mt-6 bg-white shadow-sm rounded-lg divide-y dark:bg-gray-700 dark:text-white">
    @foreach ($habits as $habit)
        <div class="p-6 flex space-x-2" wire:key="{{ $habit->id }}">
            <div class="flex-1">
                <div class="flex justify-between items-center">
                    <div class="flex">
                        <span class="text-gray-800 dark:text-gray-200">{{ $habit->name }}</span>
                    </div>
                    <div class="flex p-2">
                        <livewire:counts.list-single :habit=$habit wire:key="$habit->id" />
                    </div>
                    <div>
                        <x-dropdown>
                            <x-slot name="trigger">
                                <button>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                    </svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link wire:click="edit({{ $habit->id }})">
                                    {{ __('Edit') }}
                                </x-dropdown-link>
                                <x-dropdown-link href="{{route('counts', ['habit' => $habit->id])}}">History</x-dropdown-link>
                                <x-dropdown-link wire:click="delete({{ $habit->id }})" wire:confirm="Are you sure to delete this habit?"> 
                                    {{ __('Delete') }}
                                </x-dropdown-link> 
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
                @if ($habit->is($editing)) 
                    <livewire:habits.edit :habit="$habit" :key="$habit->id" />
                @endif 
            </div>
        </div>
    @endforeach 
</div>