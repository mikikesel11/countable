<?php

use Livewire\Volt\Component;
use App\Models\Habit;

new class extends Component {
    public Habit $habit;
}; ?>
<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <livewire:counts.create :habit=$habit />
        <livewire:counts.list :habit=$habit />
    </div>
</x-app-layout>