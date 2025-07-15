<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FileController extends ApiController
{
    /**
     * Upload a file.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'type' => 'sometimes|string|in:image,document,media',
            'folder' => 'sometimes|string|max:255',
        ]);

        try {
            $file = $request->file('file');
            $type = $request->input('type', 'general');
            $folder = $request->input('folder', 'uploads');

            // Generate unique filename
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $folder . '/' . $type . '/' . $filename;

            // Store file
            $storedPath = $file->storeAs($path, $filename, 'public');

            return $this->success([
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'path' => $storedPath,
                'url' => Storage::url($storedPath),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ], 'File uploaded successfully');

        } catch (\Exception $e) {
            return $this->error('File upload failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Upload an image with validation.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB max
            'folder' => 'sometimes|string|max:255',
        ]);

        try {
            $image = $request->file('image');
            $folder = $request->input('folder', 'images');

            // Generate unique filename
            $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
            $path = $folder . '/' . date('Y/m/d') . '/' . $filename;

            // Store image
            $storedPath = $image->storeAs($path, $filename, 'public');

            return $this->success([
                'filename' => $filename,
                'original_name' => $image->getClientOriginalName(),
                'path' => $storedPath,
                'url' => Storage::url($storedPath),
                'size' => $image->getSize(),
                'dimensions' => $this->getImageDimensions($image),
            ], 'Image uploaded successfully');

        } catch (\Exception $e) {
            return $this->error('Image upload failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a file.
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        try {
            $path = $request->input('path');

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                return $this->success(null, 'File deleted successfully');
            }

            return $this->notFound('File not found');

        } catch (\Exception $e) {
            return $this->error('File deletion failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get file information.
     */
    public function info(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        try {
            $path = $request->input('path');

            if (!Storage::disk('public')->exists($path)) {
                return $this->notFound('File not found');
            }

            $fullPath = Storage::disk('public')->path($path);
            
            return $this->success([
                'path' => $path,
                'url' => Storage::url($path),
                'size' => Storage::disk('public')->size($path),
                'last_modified' => Storage::disk('public')->lastModified($path),
                'mime_type' => Storage::disk('public')->mimeType($path),
                'exists' => true,
            ]);

        } catch (\Exception $e) {
            return $this->error('Failed to get file info: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get image dimensions.
     */
    private function getImageDimensions($image): array
    {
        try {
            $dimensions = getimagesize($image->path());
            return [
                'width' => $dimensions[0],
                'height' => $dimensions[1],
            ];
        } catch (\Exception $e) {
            return ['width' => null, 'height' => null];
        }
    }
} 