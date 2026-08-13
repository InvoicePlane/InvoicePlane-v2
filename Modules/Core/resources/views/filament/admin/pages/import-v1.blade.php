<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Step Navigation Bar --}}
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4">
            <div class="flex items-center space-x-4">
                <div class="flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-semibold {{ $currentStep >= 1 ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700' }}">1</span>
                    <span class="ml-2 font-medium text-sm {{ $currentStep === 1 ? 'text-primary-600 font-semibold' : 'text-gray-500' }}">1. Source & Target</span>
                </div>
                <div class="w-8 h-0.5 bg-gray-300"></div>
                <div class="flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-semibold {{ $currentStep >= 2 ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700' }}">2</span>
                    <span class="ml-2 font-medium text-sm {{ $currentStep === 2 ? 'text-primary-600 font-semibold' : 'text-gray-500' }}">2. Dry-Run & Inspection</span>
                </div>
                <div class="w-8 h-0.5 bg-gray-300"></div>
                <div class="flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-semibold {{ $currentStep >= 3 ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700' }}">3</span>
                    <span class="ml-2 font-medium text-sm {{ $currentStep === 3 ? 'text-primary-600 font-semibold' : 'text-gray-500' }}">3. Results & Invariants</span>
                </div>
            </div>

            @if ($currentStep > 1)
                <x-filament::button color="gray" wire:click="resetWizard" size="sm">
                    Reset / Start Over
                </x-filament::button>
            @endif
        </div>

        {{-- STEP 1: SOURCE CONFIGURATION --}}
        @if ($currentStep === 1)
            <x-filament::section heading="Migration Settings">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Target Company</label>
                        <select wire:model="selectedCompanyId" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                            @foreach ($this->companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }} ({{ $company->search_code }})</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">All migrated clients, invoices, quotes, products, and payments will be scoped to this company.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Table Prefix</label>
                        <input type="text" wire:model="tablePrefix" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm" placeholder="ip_">
                        <p class="text-xs text-gray-500 mt-1">Default table prefix used in v1 MySQL database (usually <code>ip_</code>).</p>
                    </div>
                </div>

                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Source Type</label>
                    <div class="flex space-x-4 mb-4">
                        <label class="inline-flex items-center">
                            <input type="radio" wire:model.live="sourceType" value="sql_file" class="text-primary-600">
                            <span class="ml-2 text-sm font-medium">Upload SQL Dump (.sql)</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" wire:model.live="sourceType" value="database" class="text-primary-600">
                            <span class="ml-2 text-sm font-medium">Direct MySQL Database Connection</span>
                        </label>
                    </div>

                    @if ($sourceType === 'sql_file')
                        <div class="p-4 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-lg text-center">
                            <input type="file" wire:model="sqlFile" accept=".sql,.txt" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                            <div wire:loading wire:target="sqlFile" class="text-xs text-primary-600 mt-2">Uploading SQL file...</div>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Host</label>
                                <input type="text" wire:model="dbHost" class="w-full rounded-lg border-gray-300 dark:border-gray-700 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Port</label>
                                <input type="text" wire:model="dbPort" class="w-full rounded-lg border-gray-300 dark:border-gray-700 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Database</label>
                                <input type="text" wire:model="dbDatabase" class="w-full rounded-lg border-gray-300 dark:border-gray-700 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                                <input type="text" wire:model="dbUsername" class="w-full rounded-lg border-gray-300 dark:border-gray-700 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                                <input type="password" wire:model="dbPassword" class="w-full rounded-lg border-gray-300 dark:border-gray-700 text-sm">
                            </div>
                            <div class="flex items-end">
                                <x-filament::button wire:click="testConnection" color="gray" size="sm" class="w-full">
                                    Test Connection
                                </x-filament::button>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end">
                    <x-filament::button wire:click="proceedToInspection" color="primary">
                        Analyze Source & Dry-Run &rarr;
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif

        {{-- STEP 2: DRY RUN & INSPECTION --}}
        @if ($currentStep === 2 && $inspectionResult)
            <x-filament::section heading="Source Data Inspection & Dry-Run Analysis">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase text-xs">
                            <tr>
                                <th class="p-3">Entity Type</th>
                                <th class="p-3 text-right">Source Records</th>
                                <th class="p-3 text-right">Will Migrate</th>
                                <th class="p-3 text-right">Unmappable / Skips</th>
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
                                <td class="p-3">Total</td>
                                <td class="p-3 text-right font-mono">{{ number_format($inspectionResult['total_source_count']) }}</td>
                                <td class="p-3 text-right font-mono text-emerald-600">{{ number_format($inspectionResult['total_will_migrate']) }}</td>
                                <td class="p-3 text-right font-mono">{{ number_format($inspectionResult['total_unmappable']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if (!empty($inspectionResult['warnings']))
                    <div class="mt-4 p-4 bg-amber-50 dark:bg-amber-950 border border-amber-200 dark:border-amber-800 rounded-lg">
                        <h4 class="text-sm font-semibold text-amber-800 dark:text-amber-200 mb-1">Warnings / Notes:</h4>
                        <ul class="list-disc pl-5 text-xs text-amber-700 dark:text-amber-300 space-y-1">
                            @foreach ($inspectionResult['warnings'] as $warning)
                                <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-6 flex justify-between">
                    <x-filament::button color="gray" wire:click="$set('currentStep', 1)">
                        &larr; Back
                    </x-filament::button>
                    <x-filament::button color="success" wire:click="runMigration" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="runMigration">Run Migration Now</span>
                        <span wire:loading wire:target="runMigration">Migrating records...</span>
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif

        {{-- STEP 3: RESULTS & INVARIANTS --}}
        @if ($currentStep === 3 && $migrationResult)
            <div class="space-y-6">
                <x-filament::section heading="Migration Results Summary">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                            <span class="text-xs text-gray-500 uppercase">Batch ID</span>
                            <div class="font-mono text-sm font-semibold mt-1">{{ $migrationResult['batch_id'] }}</div>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                            <span class="text-xs text-gray-500 uppercase">Status</span>
                            <div class="text-sm font-semibold mt-1 {{ $migrationResult['success'] ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $migrationResult['success'] ? '✓ Success' : '⚠ Completed with errors' }}
                            </div>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                            <span class="text-xs text-gray-500 uppercase">Financial Invariants</span>
                            <div class="text-sm font-semibold mt-1 {{ $migrationResult['financial_invariants']['passed'] ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $migrationResult['financial_invariants']['passed'] ? '✓ Verified (100% match)' : '⚠ ' . $migrationResult['financial_invariants']['failed_count'] . ' Mismatches' }}
                            </div>
                        </div>
                    </div>

                    <table class="w-full text-sm text-left mb-6">
                        <thead class="bg-gray-100 dark:bg-gray-800 uppercase text-xs">
                            <tr>
                                <th class="p-3">Entity</th>
                                <th class="p-3 text-right">Migrated</th>
                                <th class="p-3 text-right">Skipped</th>
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
                            <strong>Invariant Discrepancies:</strong>
                            <ul class="list-disc pl-5 mt-1">
                                @foreach ($migrationResult['financial_invariants']['mismatches'] as $m)
                                    <li>{{ $m['type'] }} #{{ $m['number'] }} ({{ $m['field'] }}): Expected {{ $m['expected'] }}, got {{ $m['actual'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($rollbackResult)
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800 mb-4">
                            Batch {{ $rollbackResult['batch_id'] }} has been rolled back successfully.
                        </div>
                    @endif

                    <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700">
                        @if (!$rollbackResult)
                            <x-filament::button color="danger" size="sm" wire:click="rollback" wire:confirm="Are you sure you want to rollback all records created in this migration batch?">
                                Rollback This Batch
                            </x-filament::button>
                        @else
                            <div></div>
                        @endif

                        <x-filament::button color="primary" wire:click="resetWizard">
                            Done
                        </x-filament::button>
                    </div>
                </x-filament::section>
            </div>
        @endif

    </div>
</x-filament-panels::page>
