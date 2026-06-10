<?php

namespace App\Http\Controllers\Supports\Users;

use App\Http\Controllers\Controller;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Http\Request;

class UsersCoursesController extends Controller
{
    public function index(Request $request, $slack)
    {

        $user = User::slack($slack);
        $searchKey = $request->search;

        $inscriptions = $user->inscriptions()->with('course');

        if ($searchKey != null) {
            $inscriptions = $inscriptions->where('title', 'like', '%'.$searchKey.'%');
        }

        $inscriptions = $inscriptions->paginate(paginationNumber());

        return view('supports.views.users.courses.index')->with([
            'inscriptions' => $inscriptions,
        ]);

    }

    public function postpone($slack)
    {
        $inscription = Inscription::slack($slack);
        $user = $inscription->user;
        $enterprise = $user->enterprise;
        $course = $inscription->course;

        return view('supports.views.users.courses.postpone')->with([
            'user' => $user,
            'course' => $course,
            'inscription' => $inscription,
            'enterprise' => $enterprise,
        ]);

    }

    public function destroy($slack)
    {
        $inscription = Inscription::slack($slack);
        $inscription->delete();

        return back();
    }
}
