<?php

use Livewire\Volt\Component;
use App\Models\Count;
use App\Models\Habit;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On; 

new class extends Component {
    public Collection $counts;
    public Habit $habit;

    public ?Count $editing = null; 

    public function mount(Habit $habit): void
    {
        if(isset($habit)) {
            $this->habit = $habit;
        }
        $this->getCounts();
    }

    #[On('count-created')]
    public function getCounts(): void
    {
        if(isset($this->habit)) {
            $this->counts = Count::where('user_id', auth()->user()->id)
                ->where('habit_id', $this->habit->id)
                ->latest()
                ->get(); 
        } else {
            $this->counts = Count::where('user_id', auth()->user()->id)
                ->latest()
                ->get(); 
        }
        
    }

    public function edit(Count $count): void
    {
        $this->editing = $count;
 
        $this->getCounts();
    } 

    #[On('count-edit-canceled')]
    #[On('count-updated')] 
    public function disableEditing(): void
    {
        $this->editing = null;
        $this->getCounts();
    }
    
    public function delete(Count $count): void
    {
        $this->authorize('delete', $count);
 
        $count->delete();
 
        $this->getCounts();
    } 
}; ?>

<div class="flex-auto flex-col mt-6 bg-white shadow-sm rounded-lg divide-y dark:bg-gray-700 dark:text-white">
    <div class="py-2 basis-1/2 mx-auto my-auto">
        <h3 class="basis-1/2 text-lg mx-auto px-6">{{$habit->name}} Counts</h3>
    </div>  
    @foreach($counts as $count) 
        <div class="p-6 flex space-x-2" wire:key="{{ $count->id }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 -scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <div class="flex-auto flex-col">
                <div class="flex-col justify-between items-center">
                    <div class="flex-auto md:flex-col">
                        <span class="ml-2 text-white-600">{{ $count->tracked_for_date }}</span>
                        @unless ($count->created_at->eq($count->updated_at))
                            <small class="text-sm px-2 text-white-600"> &middot; {{ __('edited') }}</small>
                        @endunless
                        @if($count->finalized)
                            <small class="text-sm px-2 text-white-600 dark:text-white-400"> &middot; {{ __('finalized') }}</small>
                        @endif
                    </div>    
                    <div>
                        @if($this->habit->type === "NUMBER")
                        <span class="ml-2 text-white-600 dark:text-white-600">{{ $count->current_count }}</span>
                        @elseif($this->habit->type === "CHECK" && $count->current_count > 0)
                        <span class="ml-2 text-white-600 dark:text-white-600">Completed</span>
                        @elseif($this->habit->type === "CHECK" && $count->current_count === 0)
                        <span class="ml-2 text-white-600 dark:text-white-600">Incomplete</span>
                        @endif
                    </div>
                    @if (!$this->editing && $count->user_id === auth()->user()->id)
                    <div class="flex mt-2">
                        <x-secondary-button wire:click="edit({{ $count->id }})" class="text-white bg-violet-600 hover:bg-violet-600 dark:bg-violet-800">
                            Edit Count
                        </x-secondary-button>
                    </div>
                    @endif
                </div>
                @if ($count->is($editing)) 
                    <livewire:counts.edit :count="$count" :key="$count->id" :habit="$habit"/>
                @endif 
            </div>
        </div>
    @endforeach
</div>
