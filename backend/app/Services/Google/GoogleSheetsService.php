<?php

namespace App\Services\Google;

use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\AppendValuesRequest;

class GoogleSheetsService
{
    private $sheets;
    private $spreadsheetId;

    public function __construct(string $spreadsheetId = null)
    {
        $client = $this->getGoogleClient();
        $this->sheets = new Sheets($client);
        $this->spreadsheetId = $spreadsheetId;
    }

    private function getGoogleClient()
    {
        $client = new \Google\Client();
        $client->setAuthConfig(storage_path('app/google-credentials.json'));
        $client->addScope(Sheets::SPREADSHEETS);
        $client->setAccessType('offline');

        return $client;
    }

    public function setSpreadsheetId(string $spreadsheetId): self
    {
        $this->spreadsheetId = $spreadsheetId;
        return $this;
    }

    public function getSheetData(string $range): array
    {
        $response = $this->sheets->spreadsheets_values->get($this->spreadsheetId, $range);
        return $response->getValues() ?? [];
    }

    public function appendSheetData(string $range, array $values): void
    {
        $body = new ValueRange([
            'values' => $values
        ]);

        $params = [
            'valueInputOption' => 'USER_ENTERED'
        ];

        $this->sheets->spreadsheets_values->append($this->spreadsheetId, $range, $body, $params);
    }

    public function updateSheetData(string $range, array $values): void
    {
        $body = new ValueRange([
            'values' => $values
        ]);

        $params = [
            'valueInputOption' => 'USER_ENTERED'
        ];

        $this->sheets->spreadsheets_values->update($this->spreadsheetId, $range, $body, $params);
    }

    public function addMaterial(array $materialData): void
    {
        $spreadsheetId = config('services.google.sheets_catalog_materi_id');
        $this->setSpreadsheetId($spreadsheetId);

        $row = [
            $materialData['material_id'] ?? '',
            $materialData['slug'] ?? '',
            $materialData['subject_name'] ?? '',
            $materialData['material_title'] ?? '',
            $materialData['material_description'] ?? '',
            $materialData['teacher_email'] ?? '',
            $materialData['date_release'] ?? '',
            $materialData['drive_source_file_link'] ?? '',
            $materialData['status'] ?? 'WAITING',
            now()->toISOString(),
            now()->toISOString(),
        ];

        $this->appendSheetData('Sheet1!A:J', [$row]);
    }

    public function updateDigitalMaterial(string $materialId, array $digitalData): void
    {
        $spreadsheetId = config('services.google.sheets_catalog_digital_id');
        $this->setSpreadsheetId($spreadsheetId);

        // Find existing row for this material_id
        $data = $this->getSheetData('Sheet1!A:K');
        $rowIndex = null;

        foreach ($data as $index => $row) {
            if (isset($row[0]) && $row[0] === $materialId) {
                $rowIndex = $index + 1; // +1 because sheets are 1-indexed
                break;
            }
        }

        $row = [
            $materialId,
            $digitalData['room_url'] ?? '',
            $digitalData['video_url'] ?? '',
            $digitalData['podcast_url'] ?? '',
            $digitalData['flashcard_url'] ?? '',
            $digitalData['sq3r_report_url'] ?? '',
            $digitalData['digital_status'] ?? 'PENDING',
            $digitalData['digital_error_log'] ?? '',
            now()->toISOString(),
        ];

        if ($rowIndex) {
            $this->updateSheetData("Sheet1!A{$rowIndex}:I{$rowIndex}", [$row]);
        } else {
            $this->appendSheetData('Sheet1!A:I', [$row]);
        }
    }

    public function updateClassroomMaterial(string $materialId, array $classroomData): void
    {
        $spreadsheetId = config('services.google.sheets_catalog_classroom_id');
        $this->setSpreadsheetId($spreadsheetId);

        // Find existing row for this material_id
        $data = $this->getSheetData('Sheet1!A:E');
        $rowIndex = null;

        foreach ($data as $index => $row) {
            if (isset($row[0]) && $row[0] === $materialId) {
                $rowIndex = $index + 1;
                break;
            }
        }

        $row = [
            $materialId,
            $classroomData['classroom_url'] ?? '',
            $classroomData['gform_url'] ?? '',
            $classroomData['sheetform_responses_url'] ?? '',
            $classroomData['classroom_status'] ?? 'NOT_CREATED',
            now()->toISOString(),
        ];

        if ($rowIndex) {
            $this->updateSheetData("Sheet1!A{$rowIndex}:F{$rowIndex}", [$row]);
        } else {
            $this->appendSheetData('Sheet1!A:F', [$row]);
        }
    }

    public function getScheduleForTomorrow(): array
    {
        $spreadsheetId = config('services.google.sheets_schedule_automation_id');
        $this->setSpreadsheetId($spreadsheetId);

        $data = $this->getSheetData('Sheet1!A:Q');
        $schedule = [];
        $tomorrow = now()->addDay()->format('Y-m-d');

        foreach ($data as $index => $row) {
            if (count($row) >= 12 && $row[0] === $tomorrow) {
                $schedule[] = [
                    'date_release' => $row[0] ?? '',
                    'time_trigger' => $row[1] ?? '',
                    'material_id' => $row[2] ?? '',
                    'proctor_email' => $row[3] ?? '',
                    'classgroup_email' => $row[4] ?? '',
                    'announcement_template' => $row[5] ?? '',
                    'assignment_template' => $row[6] ?? '',
                    'classroom_id' => $row[7] ?? '',
                    'assignment_url' => $row[8] ?? '',
                    'announcement_status' => $row[9] ?? 'PENDING',
                    'assignment_status' => $row[10] ?? 'PENDING',
                    'last_process_log' => $row[11] ?? '',
                    'updated_at' => $row[12] ?? '',
                    'row_index' => $index + 1,
                ];
            }
        }

        return $schedule;
    }

    public function updateScheduleStatus(int $rowIndex, array $statusData): void
    {
        $spreadsheetId = config('services.google.sheets_schedule_automation_id');
        $this->setSpreadsheetId($spreadsheetId);

        $row = [
            $statusData['date_release'] ?? '',
            $statusData['time_trigger'] ?? '',
            $statusData['material_id'] ?? '',
            $statusData['proctor_email'] ?? '',
            $statusData['classgroup_email'] ?? '',
            $statusData['announcement_template'] ?? '',
            $statusData['assignment_template'] ?? '',
            $statusData['classroom_id'] ?? '',
            $statusData['assignment_url'] ?? '',
            $statusData['announcement_status'] ?? '',
            $statusData['assignment_status'] ?? '',
            $statusData['last_process_log'] ?? '',
            now()->toISOString(),
        ];

        $this->updateSheetData("Sheet1!A{$rowIndex}:M{$rowIndex}", [$row]);
    }
}