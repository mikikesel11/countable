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

    #[Validate('required|int|min:0')]
    public $streak = 0;

    public $finalized = null;
    public $final = false;
    public $check = false;
    
    public function mount(Habit $habit): void
    {
        $this->habit = $habit;
        $this->streak = $this->habit->current_streak;
        $this->tracked_for_date = today()->format('Y-m-d');
        $this->getCount();
    }

    public function getCount(): void
    {
        $isYesterday = Carbon::yesterday()->format('Y-m-d');
        $today = today()->format('Y-m-d');
        $notFinalCount = Count::where('user_id', auth()->user()->id)
            ->where('habit_name', $this->habit->name)
            ->where('habit_id', $this->habit->id)
            ->whereNull('finalized')
            ->latest()
            ->first();     
        if($notFinalCount && $notFinalCount->tracked_for_date === $today) {
            $this->count = $notFinalCount;
            $this->current_count = $notFinalCount->current_count;
            $this->tracked_for_date = $notFinalCount->tracked_for_date;
            $this->streak = $notFinalCount->streak;
            return;
        } 
        elseif($notFinalCount && $notFinalCount->tracked_for_date === $isYesterday) 
        {
            $this->authorize('update', $notFinalCount);
            $update = array();
            $update['finalized'] = now();
            $notFinalCount->update($update);
            $this->streak = $notFinalCount->streak + 1;
        }
        else 
        {
            $streakSet = Count::where('user_id', auth()->user()->id)
                ->where('habit_id', $this->habit->id)
                ->whereNotNull('finalized')
                ->latest()
                ->first();
            if($streakSet) {
                if($streakSet->tracked_for_date === $isYesterday){
                    $this->streak = $streakSet->streak + 1;
                }
            } 
        }
        $newCount = Count::create([
                'user_id' => $this->habit->user_id,
                'habit_id' => $this->habit->id,
                'habit_name' => $this->habit->name,
                'tracked_for_date' => $this->tracked_for_date,
                'streak' => $this->streak,
                'current_count' => 0,
                'finalized' => null,
            ]);
        $this->count = $newCount;
        $this->current_count = $newCount->current_count;
        $this->tracked_for_date = $newCount->tracked_for_date;
        $this->streak = $newCount->streak;
    }

    public function update(): void
    {
        $this->authorize('update', $this->count);
        if($this->final && !$this->check) {
            $this->finalized = now();
            if($this->current_count > 0) {
                $this->streak = $this->habit->current_streak + 1;   
            }
        }
        if($this->check) {
            $this->finalized = now();
            $this->current_count = 1;
            $this->streak = $this->habit->current_streak + 1;
        } 
        $validated = $this->validate();
        $validated['habit_id'] = $this->habit->id;
        $validated['habit_name'] = $this->habit->name;
        unset($validated['check']);
        unset($validated['final']);

        $this->count->updateOrFail($validated);

        if($this->streak > $this->habit->current_streak) 
        {
            $this->authorize('update', $this->habit);
            $this->habit->updateOrFail(['current_streak' => $this->streak]);
        }

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
                <input type="checkbox" id="check" name="check" wire:model.boolean="check" wire.confirm="Are you sure you want to consider this Count Completed?" class="appearance-none mt-1 checked:bg-violet-800 dark:bg-gray-800 dark:text-white" />
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
                <input type="checkbox" wire:model.boolean="final" id="final" name="final" wire.confirm="Are you sure you want to Finalize this Count?" class="mt-1 dark:bg-gray-800 dark:text-white" />
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