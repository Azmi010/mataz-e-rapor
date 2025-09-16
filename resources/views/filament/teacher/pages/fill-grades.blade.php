<x-filament::page>
    <form wire:submit.prevent="submit" class="space-y-4">
        {{ $this->form }}
        <div class="pt-6">
            <x-filament::button type="submit" icon="heroicon-o-check-circle">
                Simpan Nilai
            </x-filament::button>
        </div>
    </form>
</x-filament::page>
