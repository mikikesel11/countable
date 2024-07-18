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
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M11 7L8 17" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M16 7L13 17" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M18 10H7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M17 14H6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path opacity="0.5" d="M2 12C2 7.28595 2 4.92893 3.46447 3.46447C4.92893 2 7.28595 2 12 2C16.714 2 19.0711 2 20.5355 3.46447C22 4.92893 22 7.28595 22 12C22 16.714 22 19.0711 20.5355 20.5355C19.0711 22 16.714 22 12 22C7.28595 22 4.92893 22 3.46447 20.5355C2 19.0711 2 16.714 2 12Z" stroke-width="1.5"/>
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
                        <x-primary-button wire:click="edit({{ $count->id }})" class="text-white bg-violet-600 hover:bg-violet-700 dark:bg-violet-800 dark:text-white">
                            Edit Count
                        </x-primary-button>
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
