<x-filament-panels::page>
    @livewire(\Filament\Widgets\AccountWidget::class)

    <x-filament::tabs>
        <x-filament::tabs.item :active="$activeTab === 'users'" wire:click="$set('activeTab', 'users')">
            User Statistics
        </x-filament::tabs.item>

        <x-filament::tabs.item :active="$activeTab === 'orders'" wire:click="$set('activeTab', 'orders')">
            Order Statistics
        </x-filament::tabs.item>

        <x-filament::tabs.item :active="$activeTab === 'products'" wire:click="$set('activeTab', 'products')">
            Product Statistics
        </x-filament::tabs.item>

        <x-filament::tabs.item :active="$activeTab === 'revenue'" wire:click="$set('activeTab', 'revenue')">
            Revenue Statistics
        </x-filament::tabs.item>
    </x-filament::tabs>

    <div class="space-y-6 mt-6">
        @foreach ($this->getVisibleWidgets() as $widget)
            @livewire($widget, [], key($widget))
        @endforeach
    </div>
</x-filament-panels::page>
