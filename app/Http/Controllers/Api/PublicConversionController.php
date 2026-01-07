<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicConversionController extends Controller
{
    /**
     * Upload MP3 and create conversion (public - no auth required)
     */
    public function store(Request $request): JsonResponse
    {
        // Debug: Log all request data
        Log::info('Upload request received', [
            'has_file' => $request->hasFile('file'),
            'all_files' => $request->allFiles(),
            'content_type' => $request->header('Content-Type'),
        ]);

        // Check if file exists
        if (!$request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบไฟล์ในคำขอ กรุณาอัปโหลดไฟล์ MP3',
                'debug' => [
                    'has_file' => false,
                    'files' => array_keys($request->allFiles()),
                ]
            ], 422);
        }

        $file = $request->file('file');

        // Check if file is valid
        if (!$file->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'ไฟล์เสียหาย: ' . $file->getErrorMessage(),
            ], 422);
        }

        $originalFilename = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        // Check if it's an MP3 file by extension
        if ($extension !== 'mp3') {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาอัปโหลดไฟล์ MP3 เท่านั้น (พบ: .' . $extension . ')',
            ], 422);
        }

        // Generate unique token for this conversion
        $token = Str::random(64);

        // Store MP3 file
        $mp3Path = $file->store('conversions/mp3', 'public');

        if (!$mp3Path) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถบันทึกไฟล์ได้',
            ], 500);
        }

        // Create conversion record (no user_id for public uploads)
        $conversion = Conversion::create([
            'user_id' => null,
            'original_filename' => $originalFilename,
            'mp3_path' => $mp3Path,
            'status' => 'pending',
            'token' => $token,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'ไฟล์ถูกอัปโหลดเรียบร้อยแล้ว',
            'data' => [
                'id' => $conversion->id,
                'original_filename' => $conversion->original_filename,
                'status' => $conversion->status,
                'created_at' => $conversion->created_at,
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Get single conversion status (public - requires token)
     */
    public function show(Request $request, Conversion $conversion): JsonResponse
    {
        $token = $request->query('token');

        if (!$token || $conversion->token !== $token) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูลหรือ token ไม่ถูกต้อง',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $conversion->id,
                'original_filename' => $conversion->original_filename,
                'status' => $conversion->status,
                'error_message' => $conversion->error_message,
                'created_at' => $conversion->created_at,
            ],
        ]);
    }

    /**
     * Download MP4 file (public - requires token)
     */
    public function download(Request $request, Conversion $conversion): JsonResponse|BinaryFileResponse
    {
        $token = $request->query('token');

        if (!$token || $conversion->token !== $token) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูลหรือ token ไม่ถูกต้อง',
            ], 404);
        }

        if (!$conversion->isCompleted() || !$conversion->mp4_path) {
            return response()->json([
                'success' => false,
                'message' => 'ไฟล์ยังไม่พร้อมสำหรับดาวน์โหลด',
            ], 400);
        }

        $filePath = Storage::disk('public')->path($conversion->mp4_path);
        $filename = pathinfo($conversion->original_filename, PATHINFO_FILENAME) . '.mp4';

        return response()->download($filePath, $filename);
    }
}
