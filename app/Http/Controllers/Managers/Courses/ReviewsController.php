<?php

namespace App\Http\Controllers\Managers\Courses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Courses\BulkActionReviewRequest;
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

        $view = request()->ajax() ? 'managers.views.courses.reviews._table' : 'managers.views.courses.reviews.index';

        return view($view)->with([
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

    public function toggleAvailable($id): JsonResponse
    {
        abort_unless(auth()->user()->can('courses.update'), 403);

        $review = CourseReview::findOrFail($id);
        $review->available = ! $review->available;
        $review->save();

        if ($review->course) {
            $review->course->recalculateRating();
        }

        return response()->json([
            'success' => true,
            'available' => $review->available,
            'message' => $review->available ? 'Reseña visible en el sitio publico.' : 'Reseña ocultada del sitio publico.',
        ]);
    }

    public function bulkAction(BulkActionReviewRequest $request): JsonResponse
    {
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
