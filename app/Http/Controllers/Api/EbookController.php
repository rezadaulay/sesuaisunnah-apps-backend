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
     * Get popular ebooks.
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $ebooks = Ebook::popular($limit)
            ->with(['creator:id,name'])
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
     * Store a newly created ebook.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'price' => 'required|numeric|min:0|max:999999.99',
            'ebook_file' => 'required|file|mimes:pdf,epub,doc,docx|max:51200', // 50MB max
            'audiobook_files.*' => 'nullable|file|mimes:mp3,m4a,aac,wav|max:102400', // 100MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Store ebook file
        $ebookFile = $request->file('ebook_file');
        $ebookFileName = time() . '_' . $ebookFile->getClientOriginalName();
        $ebookFilePath = $ebookFile->storeAs('ebooks/files', $ebookFileName, 'public');

        // Store cover image if provided
        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImage = $request->file('cover_image');
            $coverImageName = time() . '_' . $coverImage->getClientOriginalName();
            $coverImagePath = $coverImage->storeAs('ebooks/covers', $coverImageName, 'public');
        }

        // Create ebook
        $ebook = Ebook::create([
            'title' => $request->title,
            'description' => $request->description,
            'cover_image' => $coverImagePath,
            'price' => $request->price,
            'file_url' => $ebookFilePath,
            'created_by' => auth()->id() ?? 1, // Default to user ID 1 for now
        ]);

        // Store audiobook files if provided
        if ($request->hasFile('audiobook_files')) {
            $audiobookFiles = $request->file('audiobook_files');
            $orderNumber = 1;

            foreach ($audiobookFiles as $audioFile) {
                $audioFileName = time() . '_' . $orderNumber . '_' . $audioFile->getClientOriginalName();
                $audioFilePath = $audioFile->storeAs('ebooks/audiobooks/' . $ebook->id, $audioFileName, 'public');

                AudiobookFile::create([
                    'ebook_id' => $ebook->id,
                    'name' => pathinfo($audioFile->getClientOriginalName(), PATHINFO_FILENAME),
                    'file_url' => $audioFilePath,
                    'order_number' => $orderNumber,
                ]);

                $orderNumber++;
            }
        }

        $ebook->load(['creator:id,name', 'audiobookFiles']);

        return response()->json([
            'success' => true,
            'message' => 'Ebook created successfully',
            'data' => new EbookResource($ebook),
        ], 201);
    }

    /**
     * Update the specified ebook.
     */
    public function update(Request $request, Ebook $ebook): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:200',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'price' => 'sometimes|required|numeric|min:0|max:999999.99',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Update cover image if provided
        if ($request->hasFile('cover_image')) {
            // Delete old cover image
            if ($ebook->cover_image && Storage::disk('public')->exists($ebook->cover_image)) {
                Storage::disk('public')->delete($ebook->cover_image);
            }

            $coverImage = $request->file('cover_image');
            $coverImageName = time() . '_' . $coverImage->getClientOriginalName();
            $coverImagePath = $coverImage->storeAs('ebooks/covers', $coverImageName, 'public');
            
            $ebook->cover_image = $coverImagePath;
        }

        // Update other fields
        $ebook->update($request->only(['title', 'description', 'price']));

        $ebook->load(['creator:id,name', 'audiobookFiles']);

        return response()->json([
            'success' => true,
            'message' => 'Ebook updated successfully',
            'data' => new EbookResource($ebook),
        ]);
    }

    /**
     * Remove the specified ebook.
     */
    public function destroy(Ebook $ebook): JsonResponse
    {
        // Delete ebook file
        if (Storage::disk('public')->exists($ebook->file_url)) {
            Storage::disk('public')->delete($ebook->file_url);
        }

        // Delete cover image
        if ($ebook->cover_image && Storage::disk('public')->exists($ebook->cover_image)) {
            Storage::disk('public')->delete($ebook->cover_image);
        }

        // Delete audiobook files
        foreach ($ebook->audiobookFiles as $audioFile) {
            if (Storage::disk('public')->exists($audioFile->file_url)) {
                Storage::disk('public')->delete($audioFile->file_url);
            }
        }

        // Delete audiobook directory
        $audiobookDir = 'ebooks/audiobooks/' . $ebook->id;
        if (Storage::disk('public')->exists($audiobookDir)) {
            Storage::disk('public')->deleteDirectory($audiobookDir);
        }

        $ebook->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ebook deleted successfully',
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

    /**
     * Upload audiobook files to existing ebook.
     */
    public function uploadAudiobook(Request $request, Ebook $ebook): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'audiobook_files.*' => 'required|file|mimes:mp3,m4a,aac,wav|max:102400', // 100MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $uploadedFiles = [];
        $audiobookFiles = $request->file('audiobook_files');
        $orderNumber = $ebook->audiobookFiles()->max('order_number') + 1;

        foreach ($audiobookFiles as $audioFile) {
            $audioFileName = time() . '_' . $orderNumber . '_' . $audioFile->getClientOriginalName();
            $audioFilePath = $audioFile->storeAs('ebooks/audiobooks/' . $ebook->id, $audioFileName, 'public');

            $audiobookFile = AudiobookFile::create([
                'ebook_id' => $ebook->id,
                'name' => pathinfo($audioFile->getClientOriginalName(), PATHINFO_FILENAME),
                'file_url' => $audioFilePath,
                'order_number' => $orderNumber,
            ]);

            $uploadedFiles[] = $audiobookFile;
            $orderNumber++;
        }

        return response()->json([
            'success' => true,
            'message' => count($uploadedFiles) . ' audiobook file(s) uploaded successfully',
            'data' => $uploadedFiles,
        ]);
    }

    /**
     * Delete audiobook file.
     */
    public function deleteAudiobook(Request $request, Ebook $ebook): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'audiobook_file_id' => 'required|exists:audiobook_files,id,ebook_id,' . $ebook->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $audiobookFile = AudiobookFile::find($request->audiobook_file_id);

        // Delete file from storage
        if (Storage::disk('public')->exists($audiobookFile->file_url)) {
            Storage::disk('public')->delete($audiobookFile->file_url);
        }

        $audiobookFile->delete();

        return response()->json([
            'success' => true,
            'message' => 'Audiobook file deleted successfully',
        ]);
    }
}
