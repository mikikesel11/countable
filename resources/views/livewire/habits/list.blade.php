<?php

use Livewire\Volt\Component;
use Livewire\Attributes\On;
use App\Models\Habit;
use Illuminate\Database\Eloquent\Collection;

new class extends Component {
    public Collection $habits;

    public ?Habit $editing = null;

    public ?Habit $list = null;

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

    public function listCount(Habit $habit): void
    {
        $this->list = $habit;
        $this->getHabits();
    }

    #[On('count-edit')]
    public function hideCount(): void
    {
        $this->list = null;
        $this->getHabits();
    }

}; ?>

<div class="mt-6 bg-white shadow-sm rounded-lg divide-y dark:bg-gray-700 dark:text-white">
    @foreach ($habits as $habit)
        <div class="p-6 flex space-x-2" wire:key="{{ $habit->id }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 -scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <div class="flex-1">
                <div class="flex justify-between items-center">
                    <div class="flex">
                        <span class="text-gray-800 dark:text-gray-200">{{ $habit->name }}</span>
                    </div>
                    <div>
                        @if(!$editing && !$list)
                            <button class="btn btn-lg text-violet-800 dark:text-violet-300" wire:click="listCount({{$habit->id}})">Count</button>
                        @endif
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
                    @if ($list && $list->id === $habit->id) 
                        <livewire:counts.list-single :habit=$habit wire:key="$habit->id" />
                    @endif 
            </div>
        </div>
    @endforeach 
</div>