<?php

namespace Modules\Core\Mason;

use Awcodes\Mason\Mason;
use Awcodes\Mason\Support\BrickCommand;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;

/**
 * Report-flavoured replacement for Mason's stock BrickAction.
 *
 * Two things differ from the packaged action:
 *
 *  1. The node's `preview` attribute is built from toPreviewHtml(), not
 *     toHtml(). toHtml() is the print rendering and expects entity data, so
 *     using it here painted freshly inserted or edited bricks as empty
 *     boxes while bricks loaded from disk — which go through
 *     MasonDocumentConverter and therefore toPreviewHtml() — looked right.
 *  2. A brick that the band does not accept reports why instead of failing
 *     silently. Each band's canvas only registers the bricks allowed in that
 *     band, so dragging (say) a header brick onto the details canvas
 *     resolves to no brick at all.
 *
 * @see \Awcodes\Mason\Actions\BrickAction
 */
class ReportBrickAction
{
    public const NAME = 'handleBrick';

    public static function make(): Action
    {
        return Action::make(static::NAME)
            ->bootUsing(function (Action $action, array $arguments, Mason $component): ?Action {
                $brick = $component->getBrick($arguments['id']);

                if (blank($brick)) {
                    return null;
                }

                return $brick::configureBrickAction($action);
            })
            ->fillForm(fn (array $arguments): ?array => $arguments['config'] ?? null)
            ->modalHeading(function (array $arguments, Mason $component): ?string {
                $brick = $component->getBrick($arguments['id']);

                if (blank($brick)) {
                    return null;
                }

                return $brick::getLabel();
            })
            ->modalWidth(Width::Large)
            ->modalSubmitActionLabel(fn (array $arguments): ?string => match ($arguments['mode'] ?? 'insert') {
                'insert' => __('mason::mason.actions.brick.modal.actions.insert.label'),
                'edit'   => __('mason::mason.actions.brick.modal.actions.save.label'),
                default  => null,
            })
            ->action(function (array $arguments, array $data, Mason $component): void {
                $brick = $component->getBrick($arguments['id']);

                if (blank($brick)) {
                    Notification::make()
                        ->title(trans('ip.brick_not_allowed_in_band'))
                        ->danger()
                        ->send();

                    return;
                }

                $brickContent = [
                    'type'  => 'masonBrick',
                    'attrs' => [
                        'config'  => $data,
                        'id'      => $arguments['id'],
                        'label'   => $brick::getLabel(),
                        'preview' => base64_encode((string) $brick::toPreviewHtml($data)),
                    ],
                ];

                $mode  = $arguments['mode'] ?? 'insert';
                $state = $component->getState() ?? [];

                if ( ! is_array($state)) {
                    $state = [];
                }

                if ($mode === 'edit' && isset($arguments['blockIndex'])) {
                    $component->executeCommands([
                        BrickCommand::updateBrick((int) $arguments['blockIndex'], $brickContent),
                    ]);

                    return;
                }

                $position = filled($arguments['dragPosition'] ?? null)
                    ? (int) $arguments['dragPosition']
                    : count($state);

                $component->executeCommands([
                    BrickCommand::insertBrick($brickContent, $position),
                ]);
            });
    }
}
