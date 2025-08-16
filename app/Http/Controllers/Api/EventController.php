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
