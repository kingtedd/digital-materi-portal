<?php

namespace App\Services\Google;

use Illuminate\Support\Facades\Http;

class GeminiApiService
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.google.gemini_api_key');
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
    }

    public function generateContent(string $prompt, array $options = []): array
    {
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'topK' => $options['topK'] ?? 40,
                'topP' => $options['topP'] ?? 0.95,
                'maxOutputTokens' => $options['maxOutputTokens'] ?? 8192,
            ]
        ];

        $response = Http::post("{$this->baseUrl}?key={$this->apiKey}", $payload);

        if (!$response->successful()) {
            throw new \Exception("Gemini API Error: {$response->body()}");
        }

        return $response->json();
    }

    public function generateVideoScript(string $materialTitle, string $materialContent): array
    {
        $prompt = "
        Buatkan script video overview untuk materi pembelajaran dengan detail berikut:

        Judul Materi: {$materialTitle}
        Konten Materi: {$materialContent}

        Format output dalam JSON dengan struktur:
        {
            \"title\": \"Judul Video\",
            \"duration_minutes\": 5-10,
            \"script\": [
                {
                    \"scene\": 1,
                    \"narration\": \"Narasi untuk scene ini\",
                    \"visual_notes\": \"Catatan visual untuk scene ini\"
                }
            ],
            \"key_points\": [
                \"Point penting 1\",
                \"Point penting 2\"
            ]
        }

        Script harus engaging, pendek (5-10 menit), dan cocok untuk siswa sekolah menengah.
        ";

        return $this->generateContent($prompt);
    }

    public function generatePodcastScript(string $materialTitle, string $materialContent): array
    {
        $prompt = "
        Buatkan script podcast untuk materi pembelajaran dengan detail berikut:

        Judul Materi: {$materialTitle}
        Konten Materi: {$materialContent}

        Format output dalam JSON dengan struktur:
        {
            \"title\": \"Judul Podcast\",
            \"duration_minutes\": 15-20,
            \"script\": [
                {
                    \"timestamp\": \"00:00\",
                    \"speaker\": \"Host/Expert\",
                    \"text\": \"Teks pembicaraan\"
                }
            ],
            \"segments\": [
                {
                    \"title\": \"Judul Segment\",
                    \"start_time\": \"00:00\",
                    \"description\": \"Deskripsi segment\"
                }
            ]
        }

        Script harus conversational, informatif, dan cocok untuk format audio pembelajaran.
        ";

        return $this->generateContent($prompt);
    }

    public function generateFlashcards(string $materialTitle, string $materialContent, int $count = 20): array
    {
        $prompt = "
        Buatkan {$count} flashcard untuk materi pembelajaran dengan detail berikut:

        Judul Materi: {$materialTitle}
        Konten Materi: {$materialContent}

        Format output dalam JSON dengan struktur:
        {
            \"flashcards\": [
                {
                    \"id\": 1,
                    \"front\": \"Pertanyaan atau konsep di sisi depan\",
                    \"back\": \"Jawaban atau penjelasan di sisi belakang\",
                    \"category\": \"kategori pertanyaan\",
                    \"difficulty\": \"easy/medium/hard\"
                }
            ]
        }

        Flashcard harus mencakup konsep-konsep penting, variasi tingkat kesulitan, dan format yang jelas.
        ";

        return $this->generateContent($prompt);
    }

    public function generateSQ3RReport(string $materialTitle, string $materialContent): array
    {
        $prompt = "
        Buatkan laporan SQ3R (Survey, Question, Read, Recite, Review) untuk materi pembelajaran:

        Judul Materi: {$materialTitle}
        Konten Materi: {$materialContent}

        Format output dalam JSON dengan struktur:
        {
            \"survey\": {
                \"title\": \"Judul Materi\",
                \"main_topics\": [\"Topik utama 1\", \"Topik utama 2\"],
                \"structure_overview\": \"Gambaran struktur materi\"
            },
            \"questions\": [
                {
                    \"chapter\": \"Bab/Section\",
                    \"questions\": [\"Pertanyaan kunci 1\", \"Pertanyaan kunci 2\"]
                }
            ],
            \"reading_guide\": {
                \"key_concepts\": [\"Konsep penting 1\", \"Konsep penting 2\"],
                \"important_details\": [\"Detail penting 1\", \"Detail penting 2\"]
            },
            \"recite_summary\": \"Ringkasan untuk recite phase\",
            \"review_questions\": [
                {
                    \"question\": \"Pertanyaan review\",
                    \"answer_hint\": \"Petunjuk jawaban\"
                }
            ]
        }
        ";

        return $this->generateContent($prompt);
    }

    public function generateQuizQuestions(string $materialTitle, string $materialContent, int $questionCount = 10): array
    {
        $prompt = "
        Buatkan {$questionCount} soal quiz untuk materi pembelajaran dengan detail berikut:

        Judul Materi: {$materialTitle}
        Konten Materi: {$materialContent}

        Format output dalam JSON dengan struktur:
        {
            \"quiz\": {
                \"title\": \"Quiz {$materialTitle}\",
                \"duration_minutes\": 30,
                \"total_points\": 100,
                \"questions\": [
                    {
                        \"id\": 1,
                        \"type\": \"multiple_choice\",
                        \"question\": \"Teks pertanyaan\",
                        \"options\": [
                            {\"key\": \"A\", \"text\": \"Opsi A\", \"is_correct\": false},
                            {\"key\": \"B\", \"text\": \"Opsi B\", \"is_correct\": true},
                            {\"key\": \"C\", \"text\": \"Opsi C\", \"is_correct\": false},
                            {\"key\": \"D\", \"text\": \"Opsi D\", \"is_correct\": false}
                        ],
                        \"points\": 10,
                        \"explanation\": \"Penjelasan jawaban\"
                    }
                ]
            }
        }

        Campurkan tipe soal: multiple choice, true/false, dan essay. Pastikan pertanyaan menguji pemahaman konsep, bukan hafalan semata.
        ";

        return $this->generateContent($prompt);
    }

    public function analyzeQuizResults(array $quizResponses, array $quizQuestions): array
    {
        $responsesText = json_encode($quizResponses, JSON_PRETTY_PRINT);
        $questionsText = json_encode($quizQuestions, JSON_PRETTY_PRINT);

        $prompt = "
        Analisis hasil quiz berikut dan berikan insight pembelajaran:

        Data Responses:
        {$responsesText}

        Data Questions:
        {$questionsText}

        Format output dalam JSON dengan struktur:
        {
            \"material_id\": \"ID_MATERI\",
            \"aggregates\": {
                \"total_respondents\": 10,
                \"avg_score\": 82.5,
                \"mastery_percent\": 68.5,
                \"distribution\": {
                    \"excellent\": 2,
                    \"good\": 4,
                    \"satisfactory\": 3,
                    \"needs_improvement\": 1
                }
            },
            \"per_student\": [
                {
                    \"email\": \"siswa1@email.com\",
                    \"score\": 85,
                    \"topics_weak\": [\"Pecahan\", \"Desimal\"],
                    \"topics_strong\": [\"Penjumlahan\"],
                    \"recommendations\": [\"Link video remedial pecahan\"]
                }
            ],
            \"per_topic\": [
                {
                    \"topic\": \"Pecahan\",
                    \"avg_score\": 70,
                    \"difficulty_level\": \"medium\",
                    \"most_wrong_answer\": \"A\",
                    \"improvement_suggestion\": \"Perlu visualisasi lebih baik\"
                }
            ],
            \"recommendations\": [
                \"Fokus pada materi pecahan di sesi remedial\",
                \"Tambahkan contoh praktik untuk desimal\",
                \"Gunakan gambar untuk penjelasan konsep sulit\"
            ],
            \"next_steps\": [
                \"Sesi remedial untuk topik sulit\",
                \"Quiz formatif untuk check understanding\",
                \"Portfolio project untuk aplikasi konsep\"
            ]
        }

        Analisis harus mendalam dan memberikan actionable insights untuk guru.
        ";

        return $this->generateContent($prompt);
    }
}