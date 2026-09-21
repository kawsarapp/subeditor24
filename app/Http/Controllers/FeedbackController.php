<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\FeedbackVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    /**
     * Display a listing of feature requests & bug reports.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $isSuperAdmin = Auth::user()->role === 'super_admin';

        $query = Feedback::query()->with('user');

        // Filter by Type
        if ($request->filled('type') && in_array($request->type, ['feature', 'bug', 'improvement'])) {
            $query->where('type', $request->type);
        }

        // Filter by Category
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter by Status / Tab
        $tab = $request->get('tab', 'all');
        if ($tab === 'my') {
            $query->where('user_id', $userId);
        } elseif ($tab === 'roadmap') {
            $query->whereIn('status', ['planned', 'in_progress']);
        } elseif (in_array($tab, ['under_review', 'planned', 'in_progress', 'completed', 'declined'])) {
            $query->where('status', $tab);
        }

        // Search query
        if ($request->filled('q')) {
            $searchTerm = '%' . $request->q . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('category', 'like', $searchTerm);
            });
        }

        // Sorting
        $sort = $request->get('sort', 'top');
        if ($sort === 'newest') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sort === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            // Default: Top Voted
            $query->orderBy('votes_count', 'desc')->orderBy('created_at', 'desc');
        }

        $feedbacks = $query->paginate(15)->withQueryString();

        // User's voted feedback IDs for instant UI state
        $userVotedIds = $userId 
            ? FeedbackVote::where('user_id', $userId)->pluck('feedback_id')->toArray() 
            : [];

        // Statistics
        $stats = [
            'total'       => Feedback::count(),
            'in_progress' => Feedback::whereIn('status', ['planned', 'in_progress'])->count(),
            'completed'   => Feedback::where('status', 'completed')->count(),
            'my_count'    => Feedback::where('user_id', $userId)->count(),
        ];

        $categories = [
            'General',
            'AI Writer & Rewriting',
            'Central Feed & Wire',
            'Scraper & Sources',
            'Studio & Photo Cards',
            'WordPress & Publishing',
            'Social Media Auto-Post',
            'SEO Intelligence',
            'Analytics & Reports',
            'Mobile & UI/UX'
        ];

        return view('feedback.index', compact('feedbacks', 'userVotedIds', 'stats', 'categories', 'tab', 'sort', 'isSuperAdmin'));
    }

    /**
     * Store a new feature request or bug report.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'type'        => 'required|in:feature,bug,improvement',
            'category'    => 'required|string|max:60',
        ]);

        $feedback = DB::transaction(function () use ($request) {
            $item = Feedback::create([
                'user_id'     => Auth::id(),
                'type'        => $request->type,
                'title'       => strip_tags($request->title),
                'description' => strip_tags($request->description),
                'category'    => $request->category,
                'status'      => 'under_review',
                'votes_count' => 1,
            ]);

            // Auto-vote on own creation
            FeedbackVote::create([
                'feedback_id' => $item->id,
                'user_id'     => Auth::id(),
            ]);

            return $item;
        });

        return redirect()->route('feedback.index', ['sort' => 'newest'])
            ->with('success', 'Your feedback/feature request has been posted successfully! Community members can now upvote it.');
    }

    /**
     * Toggle upvote on a feedback item (AJAX).
     */
    public function toggleVote($id)
    {
        $userId = Auth::id();
        $feedback = Feedback::findOrFail($id);

        $existingVote = FeedbackVote::where('feedback_id', $feedback->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingVote) {
            $existingVote->delete();
            $voted = false;
        } else {
            FeedbackVote::create([
                'feedback_id' => $feedback->id,
                'user_id'     => $userId,
            ]);
            $voted = true;
        }

        $votesCount = FeedbackVote::where('feedback_id', $feedback->id)->count();
        $feedback->update(['votes_count' => $votesCount]);

        return response()->json([
            'success'     => true,
            'voted'       => $voted,
            'votes_count' => $votesCount,
            'message'     => $voted ? 'Upvoted!' : 'Vote removed.',
        ]);
    }

    /**
     * Super Admin update status and developer response.
     */
    public function updateStatus(Request $request, $id)
    {
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'status'         => 'required|in:under_review,planned,in_progress,completed,declined',
            'admin_response' => 'nullable|string|max:2000',
        ]);

        $feedback = Feedback::findOrFail($id);
        $feedback->update([
            'status'         => $request->status,
            'admin_response' => $request->admin_response ? strip_tags($request->admin_response) : null,
        ]);

        return back()->with('success', 'Status and response updated successfully.');
    }

    /**
     * Delete a feedback item.
     */
    public function destroy($id)
    {
        $feedback = Feedback::findOrFail($id);

        if (Auth::user()->role !== 'super_admin' && $feedback->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $feedback->delete();

        return back()->with('success', 'Feedback removed successfully.');
    }
}
