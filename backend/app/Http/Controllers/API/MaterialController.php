<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\AuditLog;
use App\Services\Google\GoogleSheetsService;
use App\Services\Google\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MaterialController extends Controller
{
    private $sheetsService;
    private $driveService;

    public function __construct(GoogleSheetsService $sheetsService, GoogleDriveService $driveService)
    {
        $this->sheetsService = $sheetsService;
        $this->driveService = $driveService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get all materials for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        try {
            $sheetData = $this->sheetsService->setSpreadsheetId(
                config('services.google.sheets_catalog_materi_id')
            )->getSheetData('Sheet1!A:J');

            // Skip header row
            $materials = [];
            foreach (array_slice($sheetData, 1) as $row) {
                if (count($row) >= 9) {
                    // Filter by teacher email if not admin
                    if (!$user->isAdmin() && ($row[5] ?? '') !== $user->email) {
                        continue;
                    }

                    $materials[] = [
                        'material_id' => $row[0] ?? '',
                        'slug' => $row[1] ?? '',
                        'subject_name' => $row[2] ?? '',
                        'material_title' => $row[3] ?? '',
                        'material_description' => $row[4] ?? '',
                        'teacher_email' => $row[5] ?? '',
                        'date_release' => $row[6] ?? '',
                        'drive_source_file_link' => $row[7] ?? '',
                        'status' => $row[8] ?? 'WAITING',
                        'created_at' => $row[9] ?? '',
                        'updated_at' => $row[10] ?? '',
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $materials,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching materials: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data materi',
            ], 500);
        }
    }

    /**
     * Store a new material.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject_name' => 'required|string|max:255',
            'material_title' => 'required|string|max:255',
            'material_description' => 'required|string',
            'date_release' => 'required|date',
            'source_file' => 'required|file|mimes:pdf,ppt,pptx,doc,docx|max:10240', // 10MB max
        ]);

        $user = Auth::user();

        try {
            // Generate material ID and slug
            $materialId = 'MTR' . strtoupper(Str::random(8));
            $slug = Str::slug($request->material_title) . '-' . strtolower(Str::random(4));

            // Create folder in Google Drive
            $folderId = $this->driveService->createMaterialFolder($materialId, $slug);

            // Upload source file to Google Drive
            $uploadedFile = $this->driveService->uploadFile(
                $request->file('source_file'),
                $folderId . '/source',
                $request->file('source_file')->getClientOriginalName()
            );

            // Prepare material data
            $materialData = [
                'material_id' => $materialId,
                'slug' => $slug,
                'subject_name' => $request->subject_name,
                'material_title' => $request->material_title,
                'material_description' => $request->material_description,
                'teacher_email' => $user->email,
                'date_release' => Carbon::parse($request->date_release)->format('Y-m-d'),
                'drive_source_file_link' => $uploadedFile['view_url'],
                'status' => 'WAITING',
            ];

            // Add to Google Sheets
            $this->sheetsService->addMaterial($materialData);

            // Create job for digital content generation
            $job = Job::create([
                'material_id' => $materialId,
                'user_id' => $user->id,
                'action' => 'generate_digital',
                'status' => 'pending',
                'payload_json' => [
                    'material_data' => $materialData,
                    'folder_id' => $folderId,
                ],
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => $user->id,
                'resource' => 'material',
                'action' => 'create',
                'resource_id' => $materialId,
                'details' => $materialData,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Trigger n8n workflow (if configured)
            if ($webhookUrl = config('services.n8n.webhook_url')) {
                // This would be implemented with an HTTP client to call n8n
                // For now, we'll just log it
                \Log::info('n8n webhook would be called for job: ' . $job->id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Materi berhasil ditambahkan',
                'data' => [
                    'material_id' => $materialId,
                    'job_id' => $job->id,
                ],
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error creating material: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan materi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get material details by ID.
     */
    public function show(Request $request, string $materialId)
    {
        $user = Auth::user();

        try {
            // Get material data from catalog sheet
            $sheetData = $this->sheetsService->setSpreadsheetId(
                config('services.google.sheets_catalog_materi_id')
            )->getSheetData('Sheet1!A:J');

            $material = null;
            foreach ($sheetData as $row) {
                if (count($row) >= 9 && ($row[0] ?? '') === $materialId) {
                    // Check permission
                    if (!$user->isAdmin() && ($row[5] ?? '') !== $user->email) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Unauthorized access to this material',
                        ], 403);
                    }

                    $material = [
                        'material_id' => $row[0] ?? '',
                        'slug' => $row[1] ?? '',
                        'subject_name' => $row[2] ?? '',
                        'material_title' => $row[3] ?? '',
                        'material_description' => $row[4] ?? '',
                        'teacher_email' => $row[5] ?? '',
                        'date_release' => $row[6] ?? '',
                        'drive_source_file_link' => $row[7] ?? '',
                        'status' => $row[8] ?? 'WAITING',
                        'created_at' => $row[9] ?? '',
                        'updated_at' => $row[10] ?? '',
                    ];
                    break;
                }
            }

            if (!$material) {
                return response()->json([
                    'success' => false,
                    'message' => 'Materi tidak ditemukan',
                ], 404);
            }

            // Get digital content data
            $digitalSheetData = $this->sheetsService->setSpreadsheetId(
                config('services.google.sheets_catalog_digital_id')
            )->getSheetData('Sheet1!A:I');

            $digitalContent = null;
            foreach ($digitalSheetData as $row) {
                if (count($row) >= 8 && ($row[0] ?? '') === $materialId) {
                    $digitalContent = [
                        'material_id' => $row[0] ?? '',
                        'room_url' => $row[1] ?? '',
                        'video_url' => $row[2] ?? '',
                        'podcast_url' => $row[3] ?? '',
                        'flashcard_url' => $row[4] ?? '',
                        'sq3r_report_url' => $row[5] ?? '',
                        'digital_status' => $row[6] ?? 'PENDING',
                        'digital_error_log' => $row[7] ?? '',
                        'updated_at' => $row[8] ?? '',
                    ];
                    break;
                }
            }

            // Get classroom data
            $classroomSheetData = $this->sheetsService->setSpreadsheetId(
                config('services.google.sheets_catalog_classroom_id')
            )->getSheetData('Sheet1!A:F');

            $classroomData = null;
            foreach ($classroomSheetData as $row) {
                if (count($row) >= 5 && ($row[0] ?? '') === $materialId) {
                    $classroomData = [
                        'material_id' => $row[0] ?? '',
                        'classroom_url' => $row[1] ?? '',
                        'gform_url' => $row[2] ?? '',
                        'sheetform_responses_url' => $row[3] ?? '',
                        'classroom_status' => $row[4] ?? 'NOT_CREATED',
                        'updated_at' => $row[5] ?? '',
                    ];
                    break;
                }
            }

            // Get job history
            $jobs = Job::where('material_id', $materialId)
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'material' => $material,
                    'digital_content' => $digitalContent,
                    'classroom_data' => $classroomData,
                    'jobs' => $jobs,
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching material details: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail materi',
            ], 500);
        }
    }

    /**
     * Trigger digital content generation.
     */
    public function generateDigital(Request $request, string $materialId)
    {
        $user = Auth::user();

        try {
            // Check if user has permission for this material
            // (This would involve checking the Google Sheets as in show method)

            $job = Job::create([
                'material_id' => $materialId,
                'user_id' => $user->id,
                'action' => 'generate_digital',
                'status' => 'pending',
                'payload_json' => [
                    'triggered_by' => 'user_request',
                ],
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => $user->id,
                'resource' => 'job',
                'action' => 'create',
                'resource_id' => $job->id,
                'details' => [
                    'material_id' => $materialId,
                    'action' => 'generate_digital',
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proses pembuatan konten digital dimulai',
                'data' => [
                    'job_id' => $job->id,
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error triggering digital generation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memulai proses pembuatan konten digital',
            ], 500);
        }
    }

    /**
     * Get job status.
     */
    public function jobStatus(Request $request, int $jobId)
    {
        $user = Auth::user();

        $job = Job::where('id', $jobId)
            ->where('user_id', $user->id)
            ->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $job,
        ]);
    }
}