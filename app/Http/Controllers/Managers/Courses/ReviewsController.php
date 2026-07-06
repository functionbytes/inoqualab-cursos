<?php

namespace App\Http\Controllers\Managers\Courses;

use App\Http\Controllers\Controller;
use App\Models\Course\CourseReview;
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
}
