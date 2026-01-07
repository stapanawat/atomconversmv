<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ConversionController extends Controller
{
    /**
     * Get all conversions for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $conversions = $request->user()
            ->conversions()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $conversions,
        ]);
    }

    /**
     * Upload MP3 and create conversion
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:mp3|max:51200', // 50MB max
        ]);

        $file = $request->file('file');
        $originalFilename = $file->getClientOriginalName();

        // Store MP3 file
        $mp3Path = $file->store('conversions/mp3', 'public');

        // Create conversion record
        $conversion = $request->user()->conversions()->create([
            'original_filename' => $originalFilename,
            'mp3_path' => $mp3Path,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'ไฟล์ถูกอัปโหลดเรียบร้อยแล้ว',
            'data' => $conversion,
        ], 201);
    }

    /**
     * Get single conversion status
     */
    public function show(Request $request, Conversion $conversion): JsonResponse
    {
        // Ensure user owns this conversion
        if ($conversion->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูล',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $conversion,
        ]);
    }

    /**
     * Delete conversion
     */
    public function destroy(Request $request, Conversion $conversion): JsonResponse
    {
        // Ensure user owns this conversion
        if ($conversion->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูล',
            ], 404);
        }

        // Delete files
        if ($conversion->mp3_path) {
            Storage::disk('public')->delete($conversion->mp3_path);
        }
        if ($conversion->mp4_path) {
            Storage::disk('public')->delete($conversion->mp4_path);
        }

        $conversion->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบเรียบร้อยแล้ว',
        ]);
    }

    /**
     * Download MP4 file
     */
    public function download(Request $request, Conversion $conversion): JsonResponse|BinaryFileResponse
    {
        // Ensure user owns this conversion
        if ($conversion->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูล',
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

    // ============================================
    // Endpoints for Python Backend
    // ============================================

    /**
     * Get pending conversions (for Python backend)
     */
    public function pending(): JsonResponse
    {
        $conversions = Conversion::where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($conversion) {
                return [
                    'id' => $conversion->id,
                    'original_filename' => $conversion->original_filename,
                    'mp3_url' => Storage::disk('public')->url($conversion->mp3_path),
                    'mp3_path' => Storage::disk('public')->path($conversion->mp3_path),
                    'created_at' => $conversion->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $conversions,
        ]);
    }

    /**
     * Mark conversion as processing (for Python backend)
     */
    public function markProcessing(Conversion $conversion): JsonResponse
    {
        $conversion->update(['status' => 'processing']);

        return response()->json([
            'success' => true,
            'message' => 'สถานะเปลี่ยนเป็น processing',
        ]);
    }

    /**
     * Complete conversion with MP4 (for Python backend)
     */
    public function complete(Request $request, Conversion $conversion): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:mp4|max:512000', // 500MB max
        ]);

        $file = $request->file('file');
        $mp4Path = $file->store('conversions/mp4', 'public');

        $conversion->update([
            'mp4_path' => $mp4Path,
            'status' => 'completed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'การแปลงเสร็จสมบูรณ์',
        ]);
    }

    /**
     * Mark conversion as failed (for Python backend)
     */
    public function fail(Request $request, Conversion $conversion): JsonResponse
    {
        $request->validate([
            'error_message' => 'nullable|string|max:1000',
        ]);

        $conversion->update([
            'status' => 'failed',
            'error_message' => $request->input('error_message', 'เกิดข้อผิดพลาดในการแปลงไฟล์'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'บันทึกสถานะ failed แล้ว',
        ]);
    }
}
