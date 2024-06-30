<?php

use Carbon\Carbon;
use App\Models\Habit;
use App\Models\Count;
use Livewire\Volt\Component;
use Livewire\Attributes\Validate; 
use Livewire\Attributes\On;

new class extends Component {
    public ?Count $count;
    public Habit $habit;

    public ?Count $editing = null;
    
    #[Validate('required|int|min:0', message: 'Count Required')]
    public $current_count = 0;

    #[Validate('required|date', message: 'Tracked for Date Required')]
    public $tracked_for_date;

    public $finalized = null;
    public $final = false;
    public $check = false;
    
    public function mount(Habit $habit): void
    {
        $this->habit = $habit;
        $this->tracked_for_date = today()->format('Y-m-d');
        $this->getCount();
    }

    public function getCount(): void
    {
        $isYesterday = Carbon::yesterday()->format('Y-m-d');
        $today = today()->format('Y-m-d');
        $lastCount = Count::where('user_id', auth()->user()->id)
            ->where('habit_name', $this->habit->name)
            ->where('habit_id', $this->habit->id)
            ->latest()
            ->first();
        if($lastCount && $lastCount->tracked_for_date === $today && $lastCount->finalized === null)
        {
            $this->count = $lastCount;
            $this->current_count = $lastCount->current_count;
            $this->tracked_for_date = $lastCount->tracked_for_date;
            if($this->habit->type === "CHECK" && $this->current_count > 0)
            {
                $this->check = true;
            }
            return;
        } 
        elseif($lastCount &&  $lastCount->tracked_for_date === $isYesterday && $lastCount->finalized === null)
        {
            $this->authorize('update', $lastCount);
            $update = array();
            $update['finalized'] = now();
            $lastCount->update($update);
        }
        elseif($lastCount && $lastCount->tracked_for_date === $today)
        {
            $this->authorize('update', $lastCount);
            $update = array();
            $update['current_count'] = $lastCount->current_count + $this->current_count;
            $lastCount->update($update);
            $this->count = $lastCount;
            $this->tracked_for_date = $lastCount->tracked_for_date;
            $this->current_count = $lastCount->current_count;
            $this->finalized = $lastCount->finalized;
            if($this->habit->type === "CHECK" && $this->current_count > 0)
            {
                $this->check = true;
            }
            if($lastCount->finalized !== null)
            {
                $this->final = true;
            }
            return;
        } 
        $newCount = auth()->user()->counts()->create([
                'user_id' => $this->habit->user_id,
                'habit_id' => $this->habit->id,
                'habit_name' => $this->habit->name,
                'tracked_for_date' => $this->tracked_for_date,
                'current_count' => 0,
                'finalized' => null,
            ]);
        $this->count = $newCount;
        $this->current_count = $newCount->current_count;
        $this->tracked_for_date = $newCount->tracked_for_date;
    }

    public function update(): void
    {
        $this->authorize('update', $this->count);
        if($this->final && !$this->check && $this->count->finalized === null) {
            $this->finalized = now();
        }
        if($this->check && $this->count->finalized === null) {
            $this->finalized = now();
            $this->current_count = 1;
        } 
        $validated = $this->validate();
        $validated['habit_id'] = $this->habit->id;
        $validated['habit_name'] = $this->habit->name;

        $this->count->updateOrFail($validated);

        $this->dispatch('count-updated');
    }

    public function edit(Count $count)
    {
        $this->editing = $count;

        $this->getCount();
    }

    #[On('count-edit-canceled')]
    #[On('count-updated')] 
    public function disableEditing(): void
    {
        $this->editing = null;
 
        $this->getCount();
    } 
}; ?>

<div class="my-2 bg-white shadow-sm rounded-lg divide-y dark:bg-gray-700 dark:text-white">
    <div class="flex flex-col">
        @if(!$count->is($this->editing))
        <div class="flex flex-col space-y-2">
            @if($habit->type === "CHECK" && $count->current_count === 1)
                <span class="text-gray-800 dark:text-gray-200">Latest Count: Complete</span>
            @elseif($habit->type === "CHECK" && $count->current_count === 0)
                <span class="text-gray-800 dark:text-gray-200">Latest Count: Incomplete</span>
            @elseif($habit->type === "NUMBER")
                <span class="text-gray-800 dark:text-gray-200">Latest Count: {{$count->current_count}}</span>
            @endif
                <span class="text-gray-800 dark:text-gray-200">Tracked for: {{$count->tracked_for_date}}</span>
        </div>
        <div class="flex mt-2">
            <x-secondary-button wire:click="edit({{ $count->id }})" class="dark:bg-violet-800 bg-violet-800 hover:bg-violet-600">
                Edit Count
            </x-secondary-button>
        </div>
        @endif
        @if($count->is($editing))
        <form wire:submit="update">
        @csrf
            <div class="flex flex-col space-y-2 md:justify-between md:items-center">
                <div>
                    @if($this->habit->type === 'NUMBER')
                    <input type="number" wire:model.number="current_count" class="py-auto dark:bg-gray-800 dark:text-white" aria-label="Current Count" id="current_count" name="current_count"/>
                    <x-input-error :messages="$errors->get('current_count')" class="dark:bg-gray-800 dark:text-white" />
                    @elseIf($this->habit->type === 'CHECK')
                    <input type="checkbox" id="check" name="check" wire:model.boolean="check" class="mt-1 checked:bg-violet-800 dark:bg-gray-800 dark:text-white" />
                    <label for="check" class="px-2 dark:text-white">Complete</label>
                    <x-input-error :messages="$errors->get('check')" class="dark:bg-gray-800 dark:text-white" />
                    @endif
                </div>
                <div>
                    <input type="date" class="dark:bg-gray-800 dark:text-white" aria-label="Tracked For Date" wire:model="tracked_for_date" id="tracked_for_date" name="tracked_for_date" />
                    <x-input-error :messages="$errors->get('tracked_for_date')" class="my-2" />
                </div>
                @if($habit->type === "NUMBER")
                <div>
                    <input type="checkbox" wire:model.boolean="final" id="final" name="final" class="mt-1 dark:bg-gray-800 dark:text-white" />
                    <label for="final" class="px-2 dark:text-white">Finalize</label>
                </div>
                <div>
                    <p class="dark:text-white text-sm pt-1">'Finalize' will complete the count for Today.</p>
                </div>
                <x-input-error :messages="$errors->get('final')" class="mx-2 dark:bg-gray-800 dark:text-white" />
                @endif
                <div>
                    <x-primary-button class="btn bg-gray-200 text-black bg-violet-200 dark:bg-violet-800 dark:text-white">{{ __('Update') }}</x-primary-button>
                </div>
                @env('local')
                    <p>{{print_r($errors)}}</p>
                @endenv
            <div>
        </form>
        @endif
    </div>
</div>