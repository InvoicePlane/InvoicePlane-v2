<?php

namespace Modules\Core\Filament\Admin\Resources\ReportTemplates\Pages;

use App\Mason\Collections\ReportBricksCollection;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\MasonEditor;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Modules\Core\Filament\Admin\Resources\ReportBlocks\Schemas\ReportBlockForm;
use Modules\Core\Filament\Admin\Resources\ReportTemplates\ReportTemplateResource;
use Modules\Core\Models\ReportBlock;
use Modules\Core\Models\ReportTemplate;
use Modules\Core\Services\MasonTemplateStorage;

class ReportBuilder extends Page
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ReportTemplate $record;

    public string $masonContent = '';

    public string $selectedBlockId = '';

    public string $currentBlockSlug = '';

    protected static string $resource = ReportTemplateResource::class;

    protected string $view = 'core::filament.admin.resources.report-template-resource.pages.design-report-template';

    public function getMaxContentWidth(): string
    {
        return 'full';
    }

    public function mount(ReportTemplate $record): void
    {
        $this->record = $record;
        $this->loadMasonContent();
    }

    public function setCurrentBlockId(?string $blockId): void
    {
        if ($blockId !== null) {
            $this->currentBlockSlug = $blockId;
            \Illuminate\Support\Facades\Log::debug('ReportBuilder::setCurrentBlockId() called', [
                'blockId'          => $blockId,
                'currentBlockSlug' => $this->currentBlockSlug,
            ]);
        }
    }

    public function configureBlockAction(): Action
    {
        return Action::make('configureBlock')
            ->arguments(['blockSlug'])
            ->label(trans('ip.configure_block'))
            ->schema(fn (Schema $schema) => ReportBlockForm::configure($schema))
            ->fillForm(function (array $arguments) {
                \Illuminate\Support\Facades\Log::debug('configureBlockAction::fillForm() called', [
                    'arguments'        => $arguments,
                    'currentBlockSlug' => $this->currentBlockSlug,
                ]);

                $blockSlug = $arguments['blockSlug'] ?? $this->currentBlockSlug ?? null;

                \Illuminate\Support\Facades\Log::debug('blockSlug resolved', [
                    'blockSlug'     => $blockSlug,
                    'fromArguments' => $arguments['blockSlug'] ?? null,
                    'fromProperty'  => $this->currentBlockSlug,
                ]);

                if ( ! $blockSlug) {
                    \Illuminate\Support\Facades\Log::warning('No blockSlug provided to fillForm');

                    return [];
                }

                // Look up the block by id, slug, or block_type
                $block = ReportBlock::query()
                    ->where('id', $blockSlug)
                    ->orWhere('slug', $blockSlug)
                    ->orWhere('block_type', $blockSlug)
                    ->first();

                \Illuminate\Support\Facades\Log::debug('Block lookup result', [
                    'blockSlug'  => $blockSlug,
                    'blockFound' => $block !== null,
                    'blockId'    => $block?->id,
                    'blockName'  => $block?->name,
                ]);

                if ( ! $block) {
                    \Illuminate\Support\Facades\Log::warning('Block not found in database', [
                        'blockSlug' => $blockSlug,
                    ]);

                    return [
                        'name'          => '',
                        'width'         => 'full',
                        'block_type'    => '',
                        'data_source'   => '',
                        'default_band'  => '',
                        'is_active'     => true,
                        'fields_canvas' => [],
                    ];
                }

                // Properly extract enum values for the form
                $data = [
                    'name'          => $block->name ?? '',
                    'width'         => $block->width instanceof BackedEnum ? $block->width->value : ($block->width ?? 'full'),
                    'block_type'    => $block->block_type instanceof BackedEnum ? $block->block_type->value : ($block->block_type ?? ''),
                    'data_source'   => $block->data_source instanceof BackedEnum ? $block->data_source->value : ($block->data_source ?? ''),
                    'default_band'  => $block->default_band instanceof BackedEnum ? $block->default_band->value : ($block->default_band ?? ''),
                    'is_active'     => (bool) ($block->is_active ?? true),
                    'fields_canvas' => [],
                ];

                \Illuminate\Support\Facades\Log::debug('fillForm returning data', [
                    'blockSlug'   => $blockSlug,
                    'data'        => $data,
                    'blockRecord' => [
                        'id'           => $block->id,
                        'name'         => $block->name,
                        'width'        => $block->width,
                        'block_type'   => $block->block_type,
                        'data_source'  => $block->data_source,
                        'default_band' => $block->default_band,
                        'is_active'    => $block->is_active,
                    ],
                ]);

                return $data;
            })
            ->mountUsing(function (Schema $schema, array $arguments) {
                \Illuminate\Support\Facades\Log::debug('configureBlockAction::mountUsing() called', [
                    'arguments'        => $arguments,
                    'currentBlockSlug' => $this->currentBlockSlug,
                ]);

                $blockSlug = $arguments['blockSlug'] ?? $this->currentBlockSlug ?? null;

                \Illuminate\Support\Facades\Log::debug('mountUsing - blockSlug resolved', [
                    'blockSlug'     => $blockSlug,
                    'fromArguments' => $arguments['blockSlug'] ?? null,
                    'fromProperty'  => $this->currentBlockSlug,
                ]);

                if ( ! $blockSlug) {
                    \Illuminate\Support\Facades\Log::warning('mountUsing - No blockSlug provided');

                    return;
                }

                // Look up the block by id, slug, or block_type
                $block = ReportBlock::query()
                    ->where('id', $blockSlug)
                    ->orWhere('slug', $blockSlug)
                    ->orWhere('block_type', $blockSlug)
                    ->first();

                \Illuminate\Support\Facades\Log::debug('mountUsing - Block lookup result', [
                    'blockSlug'  => $blockSlug,
                    'blockFound' => $block !== null,
                    'blockId'    => $block?->id,
                    'blockName'  => $block?->name,
                ]);

                if ( ! $block) {
                    \Illuminate\Support\Facades\Log::warning('mountUsing - Block not found', ['blockSlug' => $blockSlug]);
                    $schema->fill([
                        'name'          => '',
                        'width'         => 'full',
                        'block_type'    => '',
                        'data_source'   => '',
                        'default_band'  => '',
                        'is_active'     => true,
                        'fields_canvas' => [],
                    ]);

                    return;
                }

                // Properly extract enum values for the form
                $data = [
                    'name'          => $block->name ?? '',
                    'width'         => $block->width instanceof BackedEnum ? $block->width->value : ($block->width ?? 'full'),
                    'block_type'    => $block->block_type instanceof BackedEnum ? $block->block_type->value : ($block->block_type ?? ''),
                    'data_source'   => $block->data_source instanceof BackedEnum ? $block->data_source->value : ($block->data_source ?? ''),
                    'default_band'  => $block->default_band instanceof BackedEnum ? $block->default_band->value : ($block->default_band ?? ''),
                    'is_active'     => (bool) ($block->is_active ?? true),
                    'fields_canvas' => [],
                ];

                \Illuminate\Support\Facades\Log::debug('mountUsing - Filling schema with data', [
                    'blockSlug'   => $blockSlug,
                    'data'        => $data,
                    'blockRecord' => [
                        'id'           => $block->id,
                        'name'         => $block->name,
                        'width'        => $block->width,
                        'block_type'   => $block->block_type,
                        'data_source'  => $block->data_source,
                        'default_band' => $block->default_band,
                        'is_active'    => $block->is_active,
                    ],
                ]);

                $schema->fill($data);
            })
            ->modalWidth(Width::FiveExtraLarge)
            ->action(function (array $data, array $arguments) {
                \Illuminate\Support\Facades\Log::debug('configureBlockAction::action() called', [
                    'arguments'        => $arguments,
                    'data'             => $data,
                    'currentBlockSlug' => $this->currentBlockSlug,
                ]);

                $blockSlug = $arguments['blockSlug'] ?? $this->currentBlockSlug ?? null;

                if ( ! $blockSlug) {
                    \Illuminate\Support\Facades\Log::warning('action - No blockSlug provided');

                    return;
                }

                \Illuminate\Support\Facades\Log::debug('action - Looking up block', ['blockSlug' => $blockSlug]);

                $block = ReportBlock::query()
                    ->where('id', $blockSlug)
                    ->orWhere('slug', $blockSlug)
                    ->orWhere('block_type', $blockSlug)
                    ->first();

                \Illuminate\Support\Facades\Log::debug('action - Block lookup result', [
                    'blockSlug'  => $blockSlug,
                    'blockFound' => $block !== null,
                    'blockId'    => $block?->id,
                ]);

                if ($block) {
                    // Extract fields from data - use fields_canvas field name
                    $fields = $data['fields_canvas'] ?? $data['fields'] ?? [];
                    unset($data['fields_canvas'], $data['fields']); // Remove fields from main data to avoid saving to DB

                    \Illuminate\Support\Facades\Log::debug('action - Updating block', [
                        'blockId'     => $block->id,
                        'updateData'  => $data,
                        'fieldsCount' => count($fields),
                    ]);

                    // Update block record
                    $block->update($data);

                    // Save fields to JSON file via service
                    if ( ! empty($fields)) {
                        $service = app(\Modules\Core\Services\ReportBlockService::class);
                        $service->saveBlockFields($block, $fields);
                        \Illuminate\Support\Facades\Log::debug('action - Fields saved', [
                            'blockId'     => $block->id,
                            'fieldsCount' => count($fields),
                        ]);
                    }

                    \Illuminate\Support\Facades\Log::info('action - Block updated successfully', [
                        'blockId'   => $block->id,
                        'blockName' => $block->name,
                    ]);
                } else {
                    \Illuminate\Support\Facades\Log::warning('action - Block not found for update', [
                        'blockSlug' => $blockSlug,
                    ]);
                }

                $this->dispatch('block-config-saved');
            })
            ->slideOver();
    }

    #[On('drag-block')]
    public function updateBlockPosition(string $blockId, array $position): void
    {
        if ( ! isset($this->blocks[$blockId])) {
            return;
        }

        $gridSnapper = app(GridSnapperService::class);
        $positionDTO = GridPositionDTO::create(
            $position['x'] ?? 0,
            $position['y'] ?? 0,
            $position['width'] ?? 1,
            $position['height'] ?? 1
        );

        if ( ! $gridSnapper->validate($positionDTO)) {
            return;
        }

        $snappedPosition = $gridSnapper->snap($positionDTO);

        $this->blocks[$blockId]['position'] = [
            'x'      => $snappedPosition->getX(),
            'y'      => $snappedPosition->getY(),
            'width'  => $snappedPosition->getWidth(),
            'height' => $snappedPosition->getHeight(),
        ];

        if (isset($position['band'])) {
            $this->blocks[$blockId]['band'] = $position['band'];
        }
    }

    #[On('add-block')]
    public function addBlock(string $blockType): void
    {
        $service      = app(ReportTemplateService::class);
        $systemBlocks = $service->getSystemBlocks();

        if (isset($systemBlocks[$blockType])) {
            $blockDto = $systemBlocks[$blockType];
            $blockId  = 'block_' . $blockType . '_' . Str::random(8);
            $blockDto->setId($blockId);

            $this->blocks[$blockId] = BlockTransformer::toArray($blockDto);

            return;
        }

        $blockId = 'block_' . $blockType . '_' . Str::random(8);

        $position = GridPositionDTO::create(0, 0, 6, 4);

        $block = new BlockDTO();
        $block->setId($blockId)
            ->setType($blockType)
            ->setSlug(null)
            ->setPosition($position)
            ->setConfig([])
            ->setLabel(ucfirst(str_replace('_', ' ', $blockType)))
            ->setIsCloneable(false)
            ->setDataSource('custom')
            ->setIsCloned(false)
            ->setClonedFrom(null);

        $this->blocks[$blockId] = BlockTransformer::toArray($block);
    }

    #[On('clone-block')]
    public function cloneBlock(string $blockId): void
    {
        if ( ! isset($this->blocks[$blockId])) {
            return;
        }

        $originalBlock = $this->blocks[$blockId];

        if ($originalBlock['isCloned'] === false && $originalBlock['isCloneable'] === true) {
            $newBlockId = 'block_' . $originalBlock['type'] . '_' . Str::random(8);

            $position = GridPositionDTO::create(
                $originalBlock['position']['x'] + 1,
                $originalBlock['position']['y'] + 1,
                $originalBlock['position']['width'],
                $originalBlock['position']['height']
            );

            $clonedBlock = new BlockDTO();
            $clonedBlock->setId($newBlockId)
                ->setType($originalBlock['type'])
                ->setSlug($originalBlock['slug'] ?? null)
                ->setPosition($position)
                ->setConfig($originalBlock['config'])
                ->setLabel($originalBlock['label'] . ' (Clone)')
                ->setIsCloneable(false)
                ->setDataSource($originalBlock['dataSource'])
                ->setIsCloned(true)
                ->setClonedFrom($blockId);

            $this->blocks[$newBlockId] = BlockTransformer::toArray($clonedBlock);
        }
    }

    #[On('delete-block')]
    public function deleteBlock(string $blockId): void
    {
        if ( ! isset($this->blocks[$blockId])) {
            return;
        }

        unset($this->blocks[$blockId]);
    }

    #[On('edit-config')]
    public function updateBlockConfig(string $blockId, array $config): void
    {
        if ( ! isset($this->blocks[$blockId])) {
            return;
        }

        $this->blocks[$blockId]['config'] = array_replace_recursive(
            $this->blocks[$blockId]['config'] ?? [],
            $config
        );
    }

    public function save($content): void
    {
        // Mason-based save: Store JSON directly
        if (is_string($content)) {
            $storage = app(MasonTemplateStorage::class);
            $storage->save($this->record, $content);
            
            $this->masonContent = $content;
            $this->dispatch('blocks-saved');
        }
    }

    /**
     * Load Mason editor content from filesystem.
     */
    protected function loadMasonContent(): void
    {
        $storage = app(MasonTemplateStorage::class);
        $this->masonContent = $storage->load($this->record);
    protected function loadMasonContent(): void
    {
        $storage = app(MasonTemplateStorage::class);
        $this->masonContent = $storage->load($this->record);
    }

    /**
     * Get Mason editor configuration.

    /**
     * Get Mason editor configuration.
     */
    public function getMasonEditorSchema(): array
    {
        return [
            MasonEditor::make('masonContent')
                ->label(trans('ip.report_layout'))
                ->bricks(ReportBricksCollection::all())
                ->preview(route('mason.preview'))
                ->dehydrated()
                ->required(),
        ];
    }

    /**
     * Get available bricks for Mason editor.
     */
    public function getAvailableBricks(): array
    {
        return ReportBricksCollection::all();
    }
}
