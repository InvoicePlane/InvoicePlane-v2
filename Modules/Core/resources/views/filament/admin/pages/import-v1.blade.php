<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Step Navigation Bar --}}
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4">
            <div class="flex items-center space-x-4">
                <div class="flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-semibold {{ $currentStep >= 1 ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700' }}">1</span>
                    <span class="ml-2 font-medium text-sm {{ $currentStep === 1 ? 'text-primary-600 font-semibold' : 'text-gray-500' }}">1. {{ trans('import.source_target') }}</span>
                </div>
                <div class="w-8 h-0.5 bg-gray-300"></div>
                <div class="flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-semibold {{ $currentStep >= 2 ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700' }}">2</span>
                    <span class="ml-2 font-medium text-sm {{ $currentStep === 2 ? 'text-primary-600 font-semibold' : 'text-gray-500' }}">2. {{ trans('import.inspection_step') }}</span>
                </div>
                <div class="w-8 h-0.5 bg-gray-300"></div>
                <div class="flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-semibold {{ $currentStep >= 3 ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700' }}">3</span>
                    <span class="ml-2 font-medium text-sm {{ $currentStep === 3 ? 'text-primary-600 font-semibold' : 'text-gray-500' }}">3. {{ trans('import.results_step') }}</span>
                </div>
            </div>

            @if ($currentStep > 1)
                <x-filament::button color="gray" wire:click="resetWizard" size="sm">
                    {{ trans('import.restart') }}
                </x-filament::button>
            @endif
        </div>

        {{-- STEP 1: SOURCE CONFIGURATION --}}
        @if ($currentStep === 1)
            <x-filament::section :heading="trans('import.settings')">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ trans('import.target_company') }}</label>
                        <select wire:model="selectedCompanyId" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                            @foreach ($this->companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }} ({{ $company->search_code }})</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">{{ trans('import.target_company_help') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ trans('import.table_prefix') }}</label>
                        <input type="text" wire:model="tablePrefix" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm" placeholder="ip_">
                        <p class="text-xs text-gray-500 mt-1">{!! trans('import.table_prefix_help') !!}</p>
                    </div>
                </div>

                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ trans('import.source_type') }}</label>
                    <div class="flex space-x-4 mb-4">
                        <label class="inline-flex items-center">
                            <input type="radio" wire:model.live="sourceType" value="sql_file" class="text-primary-600">
                            <span class="ml-2 text-sm font-medium">{{ trans('import.upload_sql') }}</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" wire:model.live="sourceType" value="database" class="text-primary-600">
                            <span class="ml-2 text-sm font-medium">{{ trans('import.direct_database') }}</span>
                        </label>
                    </div>

                    @if ($sourceType === 'sql_file')
                        <div class="p-4 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-lg text-center">
                            <input type="file" wire:model="sqlFile" accept=".sql,.txt" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                            <div wire:loading wire:target="sqlFile" class="text-xs text-primary-600 mt-2">{{ trans('import.uploading_sql') }}</div>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ trans('import.host') }}</label>
                                <input type="text" wire:model="dbHost" class="w-full rounded-lg border-gray-300 dark:border-gray-700 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ trans('ip.port') }}</label>
                                <input type="text" wire:model="dbPort" class="w-full rounded-lg border-gray-300 dark:border-gray-700 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ trans('ip.database') }}</label>
                                <input type="text" wire:model="dbDatabase" class="w-full rounded-lg border-gray-300 dark:border-gray-700 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ trans('ip.username') }}</label>
                                <input type="text" wire:model="dbUsername" class="w-full rounded-lg border-gray-300 dark:border-gray-700 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ trans('ip.password') }}</label>
                                <input type="password" wire:model="dbPassword" class="w-full rounded-lg border-gray-300 dark:border-gray-700 text-sm">
                            </div>
                            <div class="flex items-end">
                                <x-filament::button wire:click="testConnection" color="gray" size="sm" class="w-full">
                                    {{ trans('import.test_connection') }}
                                </x-filament::button>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end">
                    <x-filament::button wire:click="proceedToInspection" color="primary">
                        {{ trans('import.analyze_source') }} &rarr;
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif

        {{-- STEP 2: DRY RUN & INSPECTION --}}
        @if ($currentStep === 2 && $inspectionResult)
            <x-filament::section :heading="trans('import.inspection_heading')">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase text-xs">
                            <tr>
                                <th class="p-3">{{ trans('import.entity_type') }}</th>
                                <th class="p-3 text-right">{{ trans('import.source_records') }}</th>
                                <th class="p-3 text-right">{{ trans('import.will_migrate') }}</th>
                                <th class="p-3 text-right">{{ trans('import.unmappable') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($inspectionResult['entities'] as $entity => $data)
                                <tr>
                                    <td class="p-3 font-medium">{{ $data['label'] }}</td>
                                    <td class="p-3 text-right font-mono">{{ number_format($data['source_count']) }}</td>
                                    <td class="p-3 text-right font-mono text-emerald-600 dark:text-emerald-400 font-semibold">{{ number_format($data['will_migrate']) }}</td>
                                    <td class="p-3 text-right font-mono {{ $data['unmappable'] > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                                        {{ number_format($data['unmappable']) }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-gray-50 dark:bg-gray-800 font-bold border-t-2">
                                <td class="p-3">{{ trans('ip.total') }}</td>
                                <td class="p-3 text-right font-mono">{{ number_format($inspectionResult['total_source_count']) }}</td>
                                <td class="p-3 text-right font-mono text-emerald-600">{{ number_format($inspectionResult['total_will_migrate']) }}</td>
                                <td class="p-3 text-right font-mono">{{ number_format($inspectionResult['total_unmappable']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if (!empty($inspectionResult['warnings']))
                    <div class="mt-4 p-4 bg-amber-50 dark:bg-amber-950 border border-amber-200 dark:border-amber-800 rounded-lg">
                        <h4 class="text-sm font-semibold text-amber-800 dark:text-amber-200 mb-1">{{ trans('import.warnings') }}</h4>
                        <ul class="list-disc pl-5 text-xs text-amber-700 dark:text-amber-300 space-y-1">
                            @foreach ($inspectionResult['warnings'] as $warning)
                                <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-6 flex justify-between">
                    <x-filament::button color="gray" wire:click="$set('currentStep', 1)">
                        &larr; {{ trans('ip.prev') }}
                    </x-filament::button>
                    <x-filament::button color="success" wire:click="runMigration" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="runMigration">{{ trans('import.run_now') }}</span>
                        <span wire:loading wire:target="runMigration">{{ trans('import.migrating') }}</span>
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif

        {{-- STEP 3: RESULTS & INVARIANTS --}}
        @if ($currentStep === 3 && $migrationResult)
            <div class="space-y-6">
                <x-filament::section :heading="trans('import.results_heading')">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                            <span class="text-xs text-gray-500 uppercase">{{ trans('import.batch_id') }}</span>
                            <div class="font-mono text-sm font-semibold mt-1">{{ $migrationResult['batch_id'] }}</div>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                            <span class="text-xs text-gray-500 uppercase">{{ trans('ip.status') }}</span>
                            <div class="text-sm font-semibold mt-1 {{ $migrationResult['success'] ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $migrationResult['success'] ? '✓ ' . trans('ip.success') : '⚠ ' . trans('import.completed_with_errors') }}
                            </div>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                            <span class="text-xs text-gray-500 uppercase">{{ trans('import.financial_invariants') }}</span>
                            <div class="text-sm font-semibold mt-1 {{ $migrationResult['financial_invariants']['passed'] ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $migrationResult['financial_invariants']['passed'] ? '✓ ' . trans('import.invariants_verified') : '⚠ ' . trans('import.mismatches', ['count' => $migrationResult['financial_invariants']['failed_count']]) }}
                            </div>
                        </div>
                    </div>

                    <table class="w-full text-sm text-left mb-6">
                        <thead class="bg-gray-100 dark:bg-gray-800 uppercase text-xs">
                            <tr>
                                <th class="p-3">{{ trans('import.entity') }}</th>
                                <th class="p-3 text-right">{{ trans('import.migrated') }}</th>
                                <th class="p-3 text-right">{{ trans('import.skipped') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($migrationResult['results'] as $key => $res)
                                <tr>
                                    <td class="p-3 font-medium">{{ $res['label'] }}</td>
                                    <td class="p-3 text-right font-mono text-emerald-600 font-semibold">{{ $res['migrated'] }}</td>
                                    <td class="p-3 text-right font-mono text-gray-500">{{ $res['skipped'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if (!$migrationResult['financial_invariants']['passed'])
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg mb-6 text-xs text-amber-800">
                            <strong>{{ trans('import.invariant_discrepancies') }}</strong>
                            <ul class="list-disc pl-5 mt-1">
                                @foreach ($migrationResult['financial_invariants']['mismatches'] as $m)
                                    <li>{{ trans('import.invariant_mismatch', ['type' => $m['type'], 'number' => $m['number'], 'field' => $m['field'], 'expected' => $m['expected'], 'actual' => $m['actual']]) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($rollbackResult)
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800 mb-4">
                            {{ trans('import.batch_rolled_back', ['batch' => $rollbackResult['batch_id']]) }}
                        </div>
                    @endif

                    <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700">
                        @if (!$rollbackResult)
                            <x-filament::button color="danger" size="sm" wire:click="rollback" wire:confirm="{{ trans('import.rollback_confirm') }}">
                                {{ trans('import.rollback_batch') }}
                            </x-filament::button>
                        @else
                            <div></div>
                        @endif

                        <x-filament::button color="primary" wire:click="resetWizard">
                            {{ trans('import.done') }}
                        </x-filament::button>
                    </div>
                </x-filament::section>
            </div>
        @endif

    </div>
</x-filament-panels::page>
