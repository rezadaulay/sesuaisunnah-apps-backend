<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\EventGallery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    /**
     * Display a listing of events.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::with(['creator:id,name', 'registrations:id,event_id,user_id'])
            ->withCount('registrations');

        // Filter by status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'upcoming':
                    $query->where('event_date', '>', now());
                    break;
                case 'today':
                    $query->whereDate('event_date', now());
                    break;
                case 'past':
                    $query->where('event_date', '<', now());
                    break;
            }
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('event_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('event_date', '<=', $request->date_to);
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $events = $query->orderBy('event_date', 'asc')
            ->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $events->items(),
            'pagination' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
            ],
        ]);
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event): JsonResponse
    {
        $event->load([
            'creator:id,name',
            'registrations.user:id,name,phone,gender',
            'gallery'
        ]);
        $event->loadCount('registrations');

        return response()->json([
            'success' => true,
            'data' => new EventResource($event),
        ]);
    }

    /**
     * Get upcoming events (next 30 days).
     */
    public function upcoming(): JsonResponse
    {
        $events = Event::with(['creator:id,name'])
            ->withCount('registrations')
            ->where('event_date', '>', now())
            ->where('event_date', '<=', now()->addDays(30))
            ->orderBy('event_date', 'asc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => EventResource::collection($events),
        ]);
    }

    /**
     * Get featured events.
     */
    public function featured(): JsonResponse
    {
        $events = Event::with(['creator:id,name'])
            ->withCount('registrations')
            ->where('event_date', '>=', now())
            ->orderBy('registrations_count', 'desc')
            ->limit(3)
            ->get();

        return response()->json([
            'success' => true,
            'data' => EventResource::collection($events),
        ]);
    }

    /**
     * Upload documentation for an event.
     */
    public function uploadDocumentation(Request $request, Event $event): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'files.*' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi,pdf,doc,docx|max:10240', // 10MB max
            'type' => 'required|in:photo,video,document',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $uploadedFiles = [];
        $files = $request->file('files');

        foreach ($files as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('events/' . $event->id . '/documentation', $fileName, 'public');

            $gallery = EventGallery::create([
                'event_id' => $event->id,
                'photo_url' => $filePath,
                'type' => $request->type,
                'description' => $request->description,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);

            $uploadedFiles[] = $gallery;
        }

        return response()->json([
            'success' => true,
            'message' => count($uploadedFiles) . ' file(s) uploaded successfully',
            'data' => $uploadedFiles,
        ]);
    }

    /**
     * Get event documentation by type.
     */
    public function getDocumentation(Request $request, Event $event): JsonResponse
    {
        $type = $request->get('type', 'all');
        $query = $event->gallery();

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $documentation = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'event_date' => $event->event_date->format('Y-m-d'),
                ],
                'documentation' => $documentation->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => $item->type,
                        'file_url' => $item->full_photo_url,
                        'description' => $item->description,
                        'file_size' => $item->file_size,
                        'mime_type' => $item->mime_type,
                        'uploaded_at' => $item->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'summary' => [
                    'total_files' => $documentation->count(),
                    'photos_count' => $documentation->where('type', 'photo')->count(),
                    'videos_count' => $documentation->where('type', 'video')->count(),
                    'documents_count' => $documentation->where('type', 'document')->count(),
                ],
            ],
        ]);
    }

    /**
     * Delete event documentation.
     */
    public function deleteDocumentation(Request $request, Event $event): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'documentation_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $documentation = EventGallery::where('id', $request->documentation_id)
            ->where('event_id', $event->id)
            ->first();

        if (!$documentation) {
            return response()->json([
                'success' => false,
                'message' => 'Documentation not found for this event',
            ], 404);
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($documentation->photo_url)) {
            Storage::disk('public')->delete($documentation->photo_url);
        }

        $documentation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Documentation deleted successfully',
        ]);
    }

    /**
     * Update event documentation description.
     */
    public function updateDocumentationDescription(Request $request, Event $event): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'documentation_id' => 'required|exists:event_galleries,id,event_id,' . $event->id,
            'description' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $documentation = EventGallery::find($request->documentation_id);
        $documentation->update(['description' => $request->description]);

        return response()->json([
            'success' => true,
            'message' => 'Description updated successfully',
            'data' => [
                'id' => $documentation->id,
                'description' => $documentation->description,
                'updated_at' => $documentation->updated_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Get events with documentation summary.
     */
    public function eventsWithDocumentation(Request $request): JsonResponse
    {
        $query = Event::with(['creator:id,name'])
            ->withCount(['registrations', 'gallery'])
            ->whereHas('gallery');

        // Filter by documentation type
        if ($request->has('doc_type')) {
            $query->whereHas('gallery', function ($q) use ($request) {
                $q->where('type', $request->doc_type);
            });
        }

        $events = $query->orderBy('event_date', 'desc')
            ->paginate($request->get('per_page', 12));

        $eventsData = $events->getCollection()->map(function ($event) {
            $event->load('gallery');
            $gallerySummary = $event->gallery->groupBy('type')->map->count();

            return [
                'id' => $event->id,
                'title' => $event->title,
                'event_date' => $event->event_date->format('Y-m-d'),
                'creator' => $event->creator,
                'participants_count' => $event->registrations_count,
                'documentation_summary' => [
                    'total_files' => $event->gallery_count,
                    'photos' => $gallerySummary->get('photo', 0),
                    'videos' => $gallerySummary->get('video', 0),
                    'documents' => $gallerySummary->get('document', 0),
                ],
                'has_documentation' => $event->gallery_count > 0,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $eventsData,
            'pagination' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
            ],
        ]);
    }
}
