<?php
namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\FeedbackType;
use App\Models\FeedbackCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class FeedbackController extends Controller
{
    /**
     * ONE API: Get all types + their categories + email
     */
    public function typesWithCategories()
    {
        $types = FeedbackType::with('categories:id,type_id,name')
            ->select('id', 'name', 'email')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $types
        ]);
    }

    /**
     * Submit feedback dynamically
     */
public function submit(Request $request)
{
    try {

        // ✅ 1. Validate request
        $validated = $request->validate([
            'type_id'     => 'required|exists:feedback_types,id',
            'category_id' => 'required|exists:feedback_categories,id',
            'description' => 'required|string',
        ]);

        // ✅ 2. Auth check (extra safety)
        if (!auth()->check()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized request.',
            ], 401);
        }

        // ✅ 3. Fetch related models safely
        $type = FeedbackType::find($validated['type_id']);
        $category = FeedbackCategory::find($validated['category_id']);

        if (!$type || !$category) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid feedback type or category.',
            ], 422);
        }
        // ✅ 4. Store feedback
        $feedback = Feedback::create([
            'member_id'   => auth()->user()->id,
            'type_id'     => $validated['type_id'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
        ]);

        // ✅ 5. Send email safely
        try {
            Mail::raw("
NEW FEEDBACK RECEIVED

Member: " . auth()->user()->DisplayName . "
Type: " . ucfirst($type->name) . "
Category: " . $category->name . "

Description:
" . $validated['description'] . "
", function ($msg) use ($type) {

                $msg->from('feedback@afsc.org.in', 'AFSC');
                $msg->to($type->email);
                $msg->cc(auth()->user()->Email);
                $msg->subject('New Feedback Received');
            });

        } catch (\Exception $mailException) {
            // Email failure should NOT block feedback saving
            \Log::error('Feedback email failed', [
                'error' => $mailException->getMessage(),
                'feedback_id' => $feedback->id
            ]);
        }

        // ✅ 6. Success response
        return response()->json([
            'status' => true,
            'message' => 'Your feedback has been submitted successfully.',
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {

        return response()->json([
            'status' => false,
            'message' => 'Validation failed.',
            'errors' => $e->errors(),
        ], 422);

    } catch (\Exception $e) {

        Log::error('Feedback submit error', [
            'error' => $e->getMessage(),
            'request' => $request->all(),
        ]);

        return response()->json([
            'status' => false,
            'message' => 'Something went wrong. Please try again later.',
        ], 500);
    }
}
}
