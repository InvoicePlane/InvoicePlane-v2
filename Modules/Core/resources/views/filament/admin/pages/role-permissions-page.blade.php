<x-filament-panels::page>
    <div class="space-y-4">
        @foreach ($groupedPerms as $group => $perms)
            <x-filament::section :heading="$group" collapsible collapsed style="margin: 1rem 0;">
                <div class="overflow-x-auto my-8">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th class="text-left p-2 w-64">{{ trans('ip.permission') }}</th>
                                @foreach ($roles as $role)
                                    <th class="text-center p-2 capitalize">
                                        {{ str($role->name)->replace('_', ' ')->title() }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($perms as $perm)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="p-2">{{ $perm->label() }}</td>
                                    @foreach ($roles as $role)
                                        <td class="text-center p-2">
                                            <x-filament::input.checkbox
                                                wire:model="matrix.{{ $role->name }}.{{ $perm->value }}"
                                                :disabled="$role->name === $superAdmin"
                                            />
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>
