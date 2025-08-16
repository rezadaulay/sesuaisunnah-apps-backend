<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EbookResource;
use App\Models\Ebook;
use App\Models\AudiobookFile;
use App\Models\EbookInteraction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EbookController extends Controller
{
    /**
     * Display a listing of ebooks.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Ebook::with(['creator:id,name', 'audiobookFiles'])
            ->withCount(['interactions', 'audiobookFiles']);

        // Filter by price
        if ($request->has('price_type')) {
            switch ($request->price_type) {
                case 'free':
                    $query->free();
                    break;
                case 'paid':
                    $query->paid();
                    break;
            }
        }

        // Filter by audiobook availability
        if ($request->has('has_audiobook')) {
            if ($request->boolean('has_audiobook')) {
                $query->withAudiobook();
            }
        }

        // Search by title or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Sort by
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'title':
                $query->orderBy('title', $sortOrder);
                break;
            case 'price':
                $query->orderBy('price', $sortOrder);
                break;
            case 'popularity':
                $query->popular();
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }

        $ebooks = $query->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => EbookResource::collection($ebooks),
            'pagination' => [
                'current_page' => $ebooks->currentPage(),
                'last_page' => $ebooks->lastPage(),
                'per_page' => $ebooks->perPage(),
                'total' => $ebooks->total(),
            ],
        ]);
    }

    /**
     * Display the specified ebook.
     */
    public function show(Ebook $ebook): JsonResponse
    {
        $ebook->load([
            'creator:id,name',
            'audiobookFiles' => function ($query) {
                $query->ordered();
            },
            'interactions' => function ($query) {
                $query->recent(30)->with('user:id,name');
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => new EbookResource($ebook),
        ]);
    }

    /**
     * Get popular ebooks based on interactions.
     */
    public function popular(Request $request): JsonResponse
    {
        $ebooks = Ebook::with(['creator:id,name'])
            ->withCount(['interactions', 'audiobookFiles'])
            ->popular()
            ->limit($request->get('limit', 5))
            ->get();

        return response()->json([
            'success' => true,
            'data' => EbookResource::collection($ebooks),
        ]);
    }

    /**
     * Get free ebooks.
     */
    public function free(Request $request): JsonResponse
    {
        $ebooks = Ebook::free()
            ->with(['creator:id,name'])
            ->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => EbookResource::collection($ebooks),
            'pagination' => [
                'current_page' => $ebooks->currentPage(),
                'last_page' => $ebooks->lastPage(),
                'per_page' => $ebooks->perPage(),
                'total' => $ebooks->total(),
            ],
        ]);
    }

    /**
     * Get ebooks with audiobooks.
     */
    public function withAudiobook(Request $request): JsonResponse
    {
        $ebooks = Ebook::withAudiobook()
            ->with(['creator:id,name', 'audiobookFiles'])
            ->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => EbookResource::collection($ebooks),
            'pagination' => [
                'current_page' => $ebooks->currentPage(),
                'last_page' => $ebooks->lastPage(),
                'per_page' => $ebooks->perPage(),
                'total' => $ebooks->total(),
            ],
        ]);
    }

    /**
     * Record user interaction with ebook.
     */
    public function recordInteraction(Request $request, Ebook $ebook): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:read,download,listen',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if interaction already exists for this user and action
        $existingInteraction = EbookInteraction::where('ebook_id', $ebook->id)
            ->where('user_id', $request->user_id)
            ->where('action', $request->action)
            ->first();

        if ($existingInteraction) {
            // Update timestamp for existing interaction
            $existingInteraction->touch();
        } else {
            // Create new interaction
            EbookInteraction::create([
                'ebook_id' => $ebook->id,
                'user_id' => $request->user_id,
                'action' => $request->action,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Interaction recorded successfully',
        ]);
    }

    /**
     * Get ebook statistics.
     */
    public function statistics(Ebook $ebook): JsonResponse
    {
        $stats = [
            'total_interactions' => $ebook->total_interactions,
            'read_count' => $ebook->read_count,
            'download_count' => $ebook->download_count,
            'listen_count' => $ebook->listen_count,
            'has_audiobook' => $ebook->has_audiobook,
            'audiobook_files_count' => $ebook->audiobookFiles()->count(),
            'recent_interactions' => $ebook->interactions()
                ->recent(7)
                ->with('user:id,name')
                ->get()
                ->map(function ($interaction) {
                    return [
                        'user_name' => $interaction->user->name,
                        'action' => $interaction->action,
                        'created_at' => $interaction->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
