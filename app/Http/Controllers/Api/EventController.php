<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventApiResource;
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
            ->withCount('registrations')
            ->where('status', '!=', 'draft'); // Exclude draft events

        // Filter by status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'upcoming':
                    $query->where('start_date', '>', now());
                    break;
                case 'today':
                    $query->whereDate('start_date', now());
                    break;
                case 'past':
                    $query->where('end_date', '<', now());
                    break;
                case 'registration_open':
                    $query->canAcceptRegistrations();
                    break;
                case 'event_closed':
                    $query->closed();
                    break;
            }
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('start_date', '<=', $request->date_to);
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $events = $query->orderBy('start_date', 'asc')
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
    public function show($identifier): JsonResponse
    {
        $event = $this->findEventByIdentifier($identifier);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found'
            ], 404);
        }

        $event->load([
            'creator:id,name',
            'registrations.user:id,name,phone,gender',
            'gallery'
        ]);
        $event->loadCount('registrations');

        return response()->json([
            'success' => true,
            'data' => new EventApiResource($event),
        ]);
    }

    /**
     * Find event by ID or slug.
     */
    private function findEventByIdentifier($identifier): ?Event
    {
        // Try to find by ID first
        if (is_numeric($identifier)) {
            return Event::find($identifier);
        }

        // If not numeric, try to find by slug
        return Event::where('slug', $identifier)->first();
    }

    /**
     * Get upcoming events (next 30 days).
     */
    public function upcoming(): JsonResponse
    {
        $events = Event::with(['creator:id,name'])
            ->withCount('registrations')
            ->where('start_date', '>', now())
            ->where('start_date', '<=', now()->addDays(30))
            ->where('status', '!=', 'event_closed')
            ->where('status', '!=', 'draft') // Exclude draft events
            ->orderBy('start_date', 'asc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => EventApiResource::collection($events),
        ]);
    }

    /**
     * Get featured events.
     */
    public function featured(): JsonResponse
    {
        $events = Event::with(['creator:id,name'])
            ->withCount('registrations')
            ->where('is_featured', true) // Use is_featured field
            ->where('start_date', '>=', now())
            ->where('status', '!=', 'event_closed')
            ->where('status', '!=', 'draft') // Exclude draft events
            ->orderBy('start_date', 'asc')
            ->limit(3)
            ->get();

        return response()->json([
            'success' => true,
            'data' => EventApiResource::collection($events),
        ]);
    }

    /**
     * Get all featured events (for admin purposes).
     */
    public function allFeatured(): JsonResponse
    {
        $events = Event::with(['creator:id,name'])
            ->withCount('registrations')
            ->where('is_featured', true)
            ->where('status', '!=', 'draft') // Exclude draft events
            ->orderBy('start_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => EventApiResource::collection($events),
        ]);
    }



    /**
     * Get event documentation by type.
     */
    public function getDocumentation(Request $request, $identifier): JsonResponse
    {
        $event = $this->findEventByIdentifier($identifier);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found'
            ], 404);
        }

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
                    'start_date' => $event->start_date?->format('Y-m-d H:i'),
                    'end_date' => $event->end_date?->format('Y-m-d H:i'),
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
            ->whereHas('gallery')
            ->where('status', '!=', 'draft'); // Exclude draft events

        // Filter by documentation type
        if ($request->has('doc_type')) {
            $query->whereHas('gallery', function ($q) use ($request) {
                $q->where('type', $request->doc_type);
            });
        }

        $events = $query->orderBy('start_date', 'desc')
            ->paginate($request->get('per_page', 12));

        $eventsData = $events->getCollection()->map(function ($event) {
            $event->load('gallery');
            $gallerySummary = $event->gallery->groupBy('type')->map->count();

            return [
                'id' => $event->id,
                'title' => $event->title,
                'start_date' => $event->start_date->format('Y-m-d H:i'),
                'end_date' => $event->end_date->format('Y-m-d H:i'),
                'creator' => $event->creator,
                'participants_count' => $event->registrations_count,
                'current_participants' => $event->current_participants,
                'max_participants' => $event->max_participants,
                'registration_status' => $event->registration_status_text,
                'can_register' => $event->can_accept_registrations,
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
