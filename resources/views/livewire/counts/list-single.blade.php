<?php

use Carbon\Carbon;
use App\Models\Habit;
use App\Models\Count;
use Livewire\Volt\Component;
use Livewire\Attributes\Validate; 

new class extends Component {
    public ?Count $count;
    public Habit $habit;
    
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
            $this->streak = $lastCount->streak + 1;
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
        $newCount = Count::create([
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

        $this->dispatch('count-edit');
    }
}; ?>

<div class="my-6 bg-white shadow-sm rounded-lg divide-y dark:bg-gray-700 dark:text-white">
    <div class="flex-col basis-1/2 mx-auto my-auto">
        <form class="flex flex-col space-y-2" wire:submit="update">
            <div class="flex">
                @if($this->habit->type === 'NUMBER')
                <input type="number" wire:model.number="current_count" class="mx-4 py-auto dark:bg-gray-800 dark:text-white" aria-label="Current Count" id="current_count" name="current_count"/>
                <x-input-error :messages="$errors->get('current_count')" class="mx-2 dark:bg-gray-800 dark:text-white" />
                @elseIf($this->habit->type === 'CHECK')
                <input type="checkbox" id="check" name="check" wire:model.boolean="check" class="mt-1 checked:bg-violet-800 dark:bg-gray-800 dark:text-white" />
                <label for="check" class="px-2 dark:text-white">Complete</label>
                <x-input-error :messages="$errors->get('check')" class="mx-2 dark:bg-gray-800 dark:text-white" />
                @endif
            </div>
            <div class="flex">
                <input type="date" class="my-4 dark:bg-gray-800 dark:text-white" aria-label="Tracked For Date" wire:model="tracked_for_date" id="tracked_for_date" name="tracked_for_date" />
                <x-input-error :messages="$errors->get('tracked_for_date')" class="my-2" />
            </div>
            @if($habit->type === "NUMBER")
            <div class="flex">
                <input type="checkbox" wire:model.boolean="final" id="final" name="final" class="mt-1 dark:bg-gray-800 dark:text-white" />
                <label for="final" class="px-2 dark:text-white">Finalize</label>
            </div>
            <div class="flex">
                <p class="dark:text-white text-sm pt-1">'Finalize' will complete the count for Today.</p>
            </div>
            <x-input-error :messages="$errors->get('final')" class="mx-2 dark:bg-gray-800 dark:text-white" />
            @endif
            <div class="flex space-x-4">
                <x-primary-button class="btn bg-gray-200 text-black dark:bg-gray-800 dark:text-white">{{ __('Update') }}</x-primary-button>
            </div>
            @env('local')
                <p>{{print_r($errors)}}</p>
            @endenv
        </form>
    </div>
</div>