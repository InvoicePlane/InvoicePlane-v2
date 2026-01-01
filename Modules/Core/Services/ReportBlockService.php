<?php

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\ReportBlock;
use Throwable;

class ReportBlockService extends BaseService
{
    public function model(): string
    {
        return ReportBlock::class;
    }

    public function createReportBlock(array $data): Model
    {
        return $this->create([
            'name'         => $data['name'],
            'block_type'   => $data['block_type'],
            'slug'         => $data['slug'],
            'filename'     => $data['filename'] ?? null,
            'width'        => $data['width'],
            'data_source'  => $data['data_source'],
            'default_band' => $data['default_band'],
            'config'       => $data['config'] ?? [],
            'is_active'    => $data['is_active'] ?? true,
            'is_system'    => $data['is_system'] ?? false,
        ]);
    }

    public function updateReportBlock(ReportBlock $reportBlock, array $data): Model
    {
        $reportBlock->update([
            'name'         => $data['name'],
            'block_type'   => $data['block_type'],
            'slug'         => $data['slug'],
            'filename'     => $data['filename'] ?? null,
            'width'        => $data['width'],
            'data_source'  => $data['data_source'],
            'default_band' => $data['default_band'],
            'config'       => $data['config'] ?? [],
            'is_active'    => $data['is_active'] ?? true,
            'is_system'    => $data['is_system'] ?? false,
        ]);

        return $reportBlock;
    }

    public function deleteReportBlock(ReportBlock $reportBlock): ReportBlock
    {
        DB::beginTransaction();
        try {
            $reportBlock->delete();
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $reportBlock;
    }
}
