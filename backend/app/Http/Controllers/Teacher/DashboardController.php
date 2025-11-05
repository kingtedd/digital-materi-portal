<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\User;
use App\Services\Google\GoogleSheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private $sheetsService;

    public function __construct(GoogleSheetsService $sheetsService)
    {
        $this->sheetsService = $sheetsService;
        $this->middleware('auth');
        $this->middleware('role:teacher');
    }

    /**
     * Show the teacher dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        try {
            // Get recent jobs
            $recentJobs = Job::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            // Get materials from Google Sheets
            $materials = $this->getTeacherMaterials($user);

            // Calculate statistics
            $stats = [
                'total_materials' => count($materials),
                'published_materials' => collect($materials)->where('status', 'PUBLISHED')->count(),
                'processing_materials' => collect($materials)->where('status', 'PROCESSING')->count(),
                'waiting_materials' => collect($materials)->where('status', 'WAITING')->count(),
                'completed_jobs' => $recentJobs->where('status', 'done')->count(),
                'failed_jobs' => $recentJobs->where('status', 'failed')->count(),
                'processing_jobs' => $recentJobs->where('status', 'processing')->count(),
            ];

            return view('teacher.dashboard', compact('stats', 'recentJobs', 'materials'));

        } catch (\Exception $e) {
            \Log::error('Teacher dashboard error: ' . $e->getMessage());

            return view('teacher.dashboard', [
                'stats' => [],
                'recentJobs' => collect(),
                'materials' => [],
                'error' => 'Gagal memuat data dashboard'
            ]);
        }
    }

    /**
     * Show materials list.
     */
    public function materials(Request $request)
    {
        $user = Auth::user();

        try {
            $materials = $this->getTeacherMaterials($user);

            // Filter by status if provided
            if ($request->has('status') && $request->status !== 'all') {
                $materials = collect($materials)->where('status', $request->status)->values();
            }

            // Search by title if provided
            if ($request->has('search') && !empty($request->search)) {
                $search = strtolower($request->search);
                $materials = collect($materials)->filter(function ($material) use ($search) {
                    return str_contains(strtolower($material['material_title']), $search) ||
                           str_contains(strtolower($material['subject_name']), $search);
                })->values();
            }

            return view('teacher.materials', compact('materials'));

        } catch (\Exception $e) {
            \Log::error('Teacher materials error: ' . $e->getMessage());

            return view('teacher.materials', [
                'materials' => [],
                'error' => 'Gagal memuat data materi'
            ]);
        }
    }

    /**
     * Show create material form.
     */
    public function createMaterial(Request $request)
    {
        return view('teacher.create-material');
    }

    /**
     * Show material details.
     */
    public function showMaterial(Request $request, string $materialId)
    {
        $user = Auth::user();

        try {
            // Get material data
            $material = $this->getMaterialById($user, $materialId);
            if (!$material) {
                abort(404, 'Materi tidak ditemukan');
            }

            // Get digital content data
            $digitalContent = $this->getDigitalContent($materialId);

            // Get classroom data
            $classroomData = $this->getClassroomData($materialId);

            // Get job history
            $jobs = Job::where('material_id', $materialId)
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return view('teacher.show-material', compact(
                'material',
                'digitalContent',
                'classroomData',
                'jobs'
            ));

        } catch (\Exception $e) {
            \Log::error('Teacher show material error: ' . $e->getMessage());

            abort(500, 'Gagal memuat detail materi');
        }
    }

    /**
     * Show analytics page.
     */
    public function analytics(Request $request)
    {
        $user = Auth::user();

        try {
            $materials = $this->getTeacherMaterials($user);

            // Get materials with quiz results for analytics
            $analyticsData = [];
            foreach ($materials as $material) {
                $quizData = $this->getQuizAnalytics($material['material_id']);
                if ($quizData) {
                    $analyticsData[] = [
                        'material' => $material,
                        'analytics' => $quizData,
                    ];
                }
            }

            return view('teacher.analytics', compact('analyticsData'));

        } catch (\Exception $e) {
            \Log::error('Teacher analytics error: ' . $e->getMessage());

            return view('teacher.analytics', [
                'analyticsData' => [],
                'error' => 'Gagal memuat data analytics'
            ]);
        }
    }

    /**
     * Show profile page.
     */
    public function profile(Request $request)
    {
        $user = Auth::user();
        return view('teacher.profile', compact('user'));
    }

    /**
     * Get materials for the authenticated teacher.
     */
    private function getTeacherMaterials(User $user): array
    {
        $sheetData = $this->sheetsService->setSpreadsheetId(
            config('services.google.sheets_catalog_materi_id')
        )->getSheetData('Sheet1!A:J');

        $materials = [];
        foreach (array_slice($sheetData, 1) as $row) {
            if (count($row) >= 9 && ($row[5] ?? '') === $user->email) {
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

        return $materials;
    }

    /**
     * Get specific material by ID.
     */
    private function getMaterialById(User $user, string $materialId): ?array
    {
        $materials = $this->getTeacherMaterials($user);

        foreach ($materials as $material) {
            if ($material['material_id'] === $materialId) {
                return $material;
            }
        }

        return null;
    }

    /**
     * Get digital content for a material.
     */
    private function getDigitalContent(string $materialId): ?array
    {
        try {
            $sheetData = $this->sheetsService->setSpreadsheetId(
                config('services.google.sheets_catalog_digital_id')
            )->getSheetData('Sheet1!A:I');

            foreach ($sheetData as $row) {
                if (count($row) >= 8 && ($row[0] ?? '') === $materialId) {
                    return [
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
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error getting digital content: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get classroom data for a material.
     */
    private function getClassroomData(string $materialId): ?array
    {
        try {
            $sheetData = $this->sheetsService->setSpreadsheetId(
                config('services.google.sheets_catalog_classroom_id')
            )->getSheetData('Sheet1!A:F');

            foreach ($sheetData as $row) {
                if (count($row) >= 5 && ($row[0] ?? '') === $materialId) {
                    return [
                        'material_id' => $row[0] ?? '',
                        'classroom_url' => $row[1] ?? '',
                        'gform_url' => $row[2] ?? '',
                        'sheetform_responses_url' => $row[3] ?? '',
                        'classroom_status' => $row[4] ?? 'NOT_CREATED',
                        'updated_at' => $row[5] ?? '',
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error getting classroom data: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get quiz analytics for a material.
     */
    private function getQuizAnalytics(string $materialId): ?array
    {
        try {
            $classroomData = $this->getClassroomData($materialId);

            if ($classroomData && !empty($classroomData['sheetform_responses_url'])) {
                // This would implement fetching and analyzing quiz responses
                // For now, return mock data
                return [
                    'total_responses' => 0,
                    'avg_score' => 0,
                    'mastery_percent' => 0,
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Error getting quiz analytics: ' . $e->getMessage());
        }

        return null;
    }
}