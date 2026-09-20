<?php

namespace App\Http\Controllers\Managers\Courses;

use App\Http\Controllers\Controller;
use App\Models\Course\CourseReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewsController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('courses.view'), 403);

        $searchKey = $request->search;
        $rating = $request->rating;

        $reviews = CourseReview::with(['user', 'course'])->latest();

        if ($searchKey) {
            $reviews->where(function ($query) use ($searchKey) {
                $query->whereHas('course', fn ($c) => $c->where('title', 'like', '%'.$searchKey.'%'))
                    ->orWhereHas('user', fn ($u) => $u->where('firstname', 'like', '%'.$searchKey.'%')
                        ->orWhere('lastname', 'like', '%'.$searchKey.'%'))
                    ->orWhere('comment', 'like', '%'.$searchKey.'%');
            });
        }

        if ($rating) {
            $reviews->where('rating', $rating);
        }

        $reviews = $reviews->paginate(paginationNumber());

        return view('managers.views.courses.reviews.index')->with([
            'reviews' => $reviews,
            'searchKey' => $searchKey,
            'rating' => $rating,
        ]);
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->can('courses.delete'), 403);
        $review = CourseReview::find($id);

        if ($review) {
            $course = $review->course;
            $review->delete();

            // Actualizar el promedio del curso tras eliminar la reseña.
            if ($course) {
                $course->recalculateRating();
            }
        }

        return redirect()->route('manager.reviews');
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:course_reviews,id'],
        ]);

        abort_unless(auth()->user()->can('courses.delete'), 403);

        $reviews = CourseReview::with('course')->whereIn('id', $request->ids)->get();
        $count = $reviews->count();
        $courses = $reviews->pluck('course')->filter()->unique('id');

        foreach ($reviews as $review) {
            $review->delete();
        }

        // Recalcular el promedio de cada curso afectado, igual que destroy().
        foreach ($courses as $course) {
            $course->recalculateRating();
        }

        return response()->json(['success' => true, 'message' => $count.' reseña(s) procesados.']);
    }
}
