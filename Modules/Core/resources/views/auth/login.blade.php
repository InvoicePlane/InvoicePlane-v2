<x-filament-panels::page.simple>
    @if (filament()->hasRegistration())
        <x-slot name="subheading">
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}

            {{ $this->registerAction }}
        </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form" wire:submit="authenticate">
        <div class="mb-4">
            <x-filament::input
                id="user_email"
                name="user_email"
                type="email"
                required
                placeholder="{{ trans('ip.email') }}"
                wire:model.defer="user_email"
                autofocus
            />
        </div>

        <div class="mb-4">
            <x-filament::input
                id="user_password"
                name="user_password"
                type="password"
                required
                placeholder="{{ trans('ip.loginalert_no_password') }}"
                wire:model.defer="user_password"
            />
        </div>

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>
