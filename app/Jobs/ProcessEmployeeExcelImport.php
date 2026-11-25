<?php

namespace App\Jobs;

use App\Models\EmployeeImport;
use App\Services\EmployeeExcelImporter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessEmployeeExcelImport implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 3600;

    public function __construct(public int $employeeImportId) {}

    public function handle(EmployeeExcelImporter $importer): void
    {
        $employeeImport = EmployeeImport::findOrFail($this->employeeImportId);
        $employeeImport->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $result = $importer->import(
                Storage::disk('local')->path($employeeImport->stored_path),
                function (array $progress) use ($employeeImport): void {
                    $employeeImport->update([
                        'total_rows' => $progress['total'],
                        'processed_rows' => $progress['processed'],
                        'created_count' => $progress['created'],
                        'updated_count' => $progress['updated'],
                        'failed_count' => $progress['failed'],
                        'errors' => $progress['errors'],
                    ]);
                }
            );

            $employeeImport->update([
                'status' => 'completed',
                'total_rows' => $result['total'],
                'processed_rows' => $result['processed'],
                'created_count' => $result['created'],
                'updated_count' => $result['updated'],
                'failed_count' => $result['failed'],
                'errors' => $result['errors'],
                'finished_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $this->markFailed($exception);
            throw $exception;
        } finally {
            Storage::disk('local')->delete($employeeImport->stored_path);
        }
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception) {
            $this->markFailed($exception);
        }

        $employeeImport = EmployeeImport::find($this->employeeImportId);
        if ($employeeImport) {
            Storage::disk('local')->delete($employeeImport->stored_path);
        }
    }

    private function markFailed(Throwable $exception): void
    {
        $employeeImport = EmployeeImport::find($this->employeeImportId);
        if (! $employeeImport || in_array($employeeImport->status, ['completed', 'failed'], true)) {
            return;
        }

        $errors = $employeeImport->errors ?? [];
        $errors[] = 'Import stopped: '.$exception->getMessage();

        $employeeImport->update([
            'status' => 'failed',
            'errors' => array_slice($errors, 0, 200),
            'finished_at' => now(),
        ]);

        Log::error('Employee Excel import failed', [
            'employee_import_id' => $this->employeeImportId,
            'error' => $exception->getMessage(),
        ]);
    }
}
