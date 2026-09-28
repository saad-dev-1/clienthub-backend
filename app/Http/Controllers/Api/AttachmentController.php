<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class AttachmentController extends Controller
{
    /**
     * Allowed MIME types for upload.
     */
    protected const ALLOWED_MIMES = [
        // Images
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        // Text
        'text/plain',
        'text/csv',
        // Archives
        'application/zip',
        'application/x-zip-compressed',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
        'application/gzip',
        // Fallback (some files detect as octet-stream)
        'application/octet-stream',
    ];

    /**
     * Allowed file extensions (as fallback if mime detection fails).
     */
    protected const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'txt', 'csv',
        'zip', 'rar', '7z', 'gz',
    ];

    public function upload(Request $request, Project $project)
    {
        $this->authorizeProject($request, $project);

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240', // 10 MB
                'mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,rar,7z,gz',
            ],
        ]);

        $file = $request->file('file');

        // Detect real MIME from file contents (not client-provided)
        $realMime = $file->getMimeType();

        // Additional safety: verify mime is in our allowed list
        if (! in_array($realMime, self::ALLOWED_MIMES, true)) {
            return response()->json([
                'errors' => [
                    'file' => ['File type not allowed.'],
                ],
            ], 422);
        }

        // Sanitize original name (strip path, keep only filename)
        $originalName = basename($file->getClientOriginalName());

        // Generate safe filename
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            $extension = $file->extension() ?: 'bin';
        }

        $filename = Str::random(40) . ($extension ? '.' . $extension : '');
        $path = $file->storeAs('attachments/' . $project->id, $filename, 'local');

        $attachment = $project->attachments()->create([
            'user_id' => $request->user()->id,
            'original_name' => $originalName,
            'path' => $path,
            'mime_type' => $realMime,
            'size' => $file->getSize(),
        ]);

        return response()->json([
            'id' => $attachment->id,
            'name' => $attachment->original_name,
            'size' => $attachment->size,
            'mime_type' => $attachment->mime_type,
            'created_at' => $attachment->created_at,
            'url' => url("/api/attachments/{$attachment->id}/download"),
        ], 201);
    }

    public function index(Request $request, Project $project)
    {
        $this->authorizeProject($request, $project);

        $attachments = $project->attachments()
            ->latest()
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'name' => $a->original_name,
                    'size' => $a->size,
                    'mime_type' => $a->mime_type,
                    'created_at' => $a->created_at,
                    'url' => url("/api/attachments/{$a->id}/download"),
                ];
            });

        return response()->json($attachments);
    }

    public function download(Request $request, Attachment $attachment)
    {
        // Manually resolve user from Bearer token.
        // This route is outside the auth:sanctum middleware group, so
        // $request->user() would return null. We decode the token ourselves.
        $user = $this->resolveUserFromToken($request);

        $attachable = $attachment->attachable;

        // User is authorized if:
        // 1. They uploaded this attachment, OR
        // 2. They own the project this attachment belongs to, OR
        // 3. The project is publicly shared
        $isUploader =
            $user && (int) $attachment->user_id === (int) $user->id;
        $isProjectOwner =
            $user &&
            $attachable instanceof Project &&
            (int) $attachable->user_id === (int) $user->id;
        $isPublicProject =
            $attachable instanceof Project && (bool) $attachable->is_public;

        if (! $isUploader && ! $isProjectOwner && ! $isPublicProject) {
            abort(403, 'Unauthorized.');
        }

        if (! Storage::disk('local')->exists($attachment->path)) {
            abort(404, 'File not found.');
        }

        $fullPath = Storage::disk('local')->path($attachment->path);

        return response()->download($fullPath, $attachment->original_name);
    }

    public function destroy(Request $request, Attachment $attachment)
    {
        // Same manual token resolution — destroy can also be hit
        // from routes outside the auth middleware group in some setups.
        $user = $this->resolveUserFromToken($request);

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $attachable = $attachment->attachable;

        // Only uploader OR project owner can delete
        $isUploader = (int) $attachment->user_id === (int) $user->id;
        $isProjectOwner =
            $attachable instanceof Project &&
            (int) $attachable->user_id === (int) $user->id;

        if (! $isUploader && ! $isProjectOwner) {
            abort(403, 'Unauthorized.');
        }

        if (Storage::disk('local')->exists($attachment->path)) {
            Storage::disk('local')->delete($attachment->path);
        }

        $attachment->delete();

        return response()->json(['message' => 'Attachment deleted.']);
    }

    /**
     * Resolve the authenticated user from the Bearer token.
     * Works even when the route has no auth middleware attached.
     */
    protected function resolveUserFromToken(Request $request)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (! $accessToken) {
            return null;
        }

        return $accessToken->tokenable;
    }

    protected function authorizeProject(Request $request, Project $project): void
    {
        if (! $request->user() || (int) $project->user_id !== (int) $request->user()->id) {
            abort(403, 'Unauthorized.');
        }
    }
}