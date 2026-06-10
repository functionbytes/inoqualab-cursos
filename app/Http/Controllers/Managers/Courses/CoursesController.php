<?php

namespace App\Http\Controllers\Managers\Courses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Courses\StoreCourseRequest;
use App\Http\Requests\Managers\Courses\StoreThumbnailRequest;
use App\Http\Requests\Managers\Courses\UpdateCourseRequest;
use App\Models\Certification;
use App\Models\Certifier;
use App\Models\Course\Course;
use App\Models\Course\CourseCategorie;
use App\Models\Course\CourseChapter;
use App\Models\Course\CourseLesson;
use App\Models\Exam\ExamTopic;
use App\Models\ExamQuestion;
use App\Models\Quiz\QuizTopic;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CoursesController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;
        $website = $request->website;

        $courses = Course::descending()->with('categorie');

        if ($searchKey != null) {
            $courses = $courses->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($available != null) {
            $courses = $courses->where('available', $available);
        }

        if ($website != null) {
            $courses = $courses->where('website', $website);
        }

        $courses = $courses->paginate(paginationNumber());

        return view('managers.views.courses.courses.index')->with([
            'courses' => $courses,
            'available' => $available,
            'website' => $website,
            'searchKey' => $searchKey,
        ]);

    }

    public function navegation($slack)
    {

        $course = Course::slack($slack);

        return view('managers.views.courses.courses.navegation')->with([
            'course' => $course,
        ]);

    }

    public function create()
    {

        $categories = CourseCategorie::latest()->get();
        $categories->prepend('', '');
        $categories = $categories->pluck('title', 'id');

        $certifications = Certification::latest()->get();
        $certifications->prepend('', '');
        $certifications = $certifications->pluck('title', 'id');

        $certifiers = Certifier::latest()->get();
        $certifiers->prepend('', '');
        $certifiers = $certifiers->pluck('firstname', 'id');

        $availables = $this->availableOptions(true);

        $conditions = collect([
            ['id' => '0', 'label' => 'No'],
            ['id' => '1', 'label' => 'Si'],
        ]);
        $conditions->prepend('', '');
        $conditions = $conditions->pluck('label', 'id');

        return view('managers.views.courses.courses.create')->with([
            'categories' => $categories,
            'certifications' => $certifications,
            'certifiers' => $certifiers,
            'availables' => $availables,
            'conditions' => $conditions,
        ]);

    }

    public function edit($slack)
    {

        $course = Course::slack($slack);

        $categories = CourseCategorie::latest()->get();
        $categories = $categories->pluck('title', 'id');

        $certifications = Certification::latest()->get();
        $certifications = $certifications->pluck('title', 'id');

        $certifiers = Certifier::latest()->get();
        $certifiers = $certifiers->pluck('firstname', 'id');

        $availables = $this->availableOptions();

        $conditions = collect([
            ['id' => '0', 'label' => 'No'],
            ['id' => '1', 'label' => 'Si'],
        ]);

        $conditions = $conditions->pluck('label', 'id');

        $thumbnail = $course->getMedia('thumbnail')->count() > 0 ? 'true' : 'false';

        return view('managers.views.courses.courses.edit')->with([
            'course' => $course,
            'categories' => $categories,
            'certifications' => $certifications,
            'certifiers' => $certifiers,
            'conditions' => $conditions,
            'availables' => $availables,
            'thumbnail' => $thumbnail,
        ]);

    }

    public function duplicate($slack)
    {

        $course = Course::slack($slack);

        return view('managers.views.courses.courses.duplicate')->with([
            'course' => $course,
        ]);

    }

    public function update(UpdateCourseRequest $request)
    {
        $course = Course::slack($request->slack);

        if (! $course) {
            return response()->json(['success' => false, 'message' => 'Curso no encontrado.']);
        }

        $course->title = Str::upper($request->title);
        $course->price = $request->price;
        $course->duration = $request->duration;
        $course->discount = $request->discount;
        $course->slug = Str::slug($request->title, '-');
        $course->short = $request->short;
        $course->detail = $request->detail;
        $course->who = $request->who;
        $course->learn = $request->learn;
        $course->requirement = $request->requirement;
        $course->film = $request->film;
        $course->duration = $request->duration;
        $course->website = $request->website;
        $course->day = $request->day;
        $course->certification_id = $request->certification;
        $course->certifier_id = $request->certifier;
        $course->categorie_id = $request->categorie;
        $course->certificate = $request->certificate;
        $course->featured = $request->featured;
        $course->level = $request->level;
        $course->rating = $request->rating ?? 0;
        $course->available = $request->available;
        $course->payment = $request->payment;
        $course->exam = $request->exam;
        $course->promotion = $request->promotion;

        if ($request->promotion == 1) {
            $course->discount = $request->discount;
        } else {
            $course->discount = 0;
        }
        $course->update();

        return response()->json([
            'success' => true,
            'slack' => $course->slack,
            'message' => 'Se actualizo la clase correctamente',
        ]);

    }

    public function action(Request $request)
    {
        DB::transaction(function () use ($request) {
            $existingOpening = Course::slack($request->course);

            $newContent = $this->duplicateFile('/pages/images/course', $existingOpening->content);
            $newThumbnail = $this->duplicateFile('/pages/images/course', $existingOpening->thumbnail);

            $newOpenening = $existingOpening->replicate();
            $newOpenening->slack = $this->generate_slack('courses');
            $newOpenening->title = $request->duplicate;
            $newOpenening->slug = Str::slug($request->duplicate, '-');
            $newOpenening->content = $newContent ?? $existingOpening->content;
            $newOpenening->thumbnail = $newThumbnail ?? $existingOpening->thumbnail;
            $newOpenening->save();

            $old_topicexams = ExamTopic::where('course_id', $existingOpening->id)->get();

            foreach ($old_topicexams as $topic) {

                $new_topic = $topic->replicate()->fill(
                    [
                        'course_id' => $newOpenening->id,
                    ]
                );

                $new_topic->save();

                $old_questions = ExamQuestion::where('topic_id', $topic->id)->get();

                foreach ($old_questions as $question) {

                    $new_question = $question->replicate()->fill(
                        [
                            'topic_id' => $new_topic->id,
                            'course_id' => $newOpenening->id,
                        ]
                    );

                    $new_question->save();
                }
            }

            $old_chapter = CourseChapter::where('course_id', $existingOpening->id)->get();
            foreach ($old_chapter as $chapter) {

                $new_chapter = $chapter->replicate()->fill(
                    [
                        'course_id' => $newOpenening->id,
                    ]
                );

                $new_chapter->save();

                $old_class = CourseLesson::where('chapter_id', $chapter->id)->get();

                foreach ($old_class as $class) {

                    $newclassVideo = $this->duplicateFile('/pages/video/class', $class->video);
                    $newclassPDF = $this->duplicateFile('/pages/files/pdf', $class->pdf);
                    $newclassZIP = $this->duplicateFile('/pages/video/class', $class->zip);
                    $newclassPreview = $this->duplicateFile('/pages/video/class/preview', $class->preview_video);
                    $newclassAUDIO = $this->duplicateFile('/pages/video/class', $class->audio);
                    $newclassfile = $this->duplicateFile('/pages/files/class/material', $class->file);

                    $new_class = $class->replicate()->fill(
                        [
                            'course_id' => $newOpenening->id,
                            'coursechapter_id' => $new_chapter->id,
                            'video' => $newclassVideo,
                            'pdf' => $newclassPDF,
                            'zip' => $newclassZIP,
                            'preview_video' => $newclassPreview,
                            'audio' => $newclassAUDIO,
                            'file' => $newclassfile,
                        ]
                    );

                    $new_class->save();

                    $old_topics = QuizTopic::where('lesson_id', $class->id)->get();

                    foreach ($old_topics as $topic) {

                        $new_topic = $topic->replicate()->fill(
                            [
                                'course_id' => $newOpenening->id,
                                'lesson_id' => $new_class->id,
                            ]
                        );

                        $new_topic->save();

                        $old_questions = QuizQuestion::where('topic_id', $topic->id)->get();

                        foreach ($old_questions as $question) {

                            $new_question = $question->replicate()->fill(
                                [
                                    'topic_id' => $new_topic->id,
                                    'lesson_id' => $new_class->id,
                                ]
                            );

                            $new_question->save();
                        }
                    }
                }
            }

        }); // end DB::transaction

        return redirect()->route('manager.courses');

    }

    public function store(StoreCourseRequest $request)
    {
        $course = new Course;
        $course->slack = $this->generate_slack('courses');
        $course->title = Str::upper($request->title);
        $course->price = $request->price;
        $course->duration = $request->duration;
        $course->discount = $request->discount;
        $course->slug = Str::slug($request->title, '-');
        $course->short = $request->short;
        $course->detail = $request->detail;
        $course->who = $request->who;
        $course->learn = $request->learn;
        $course->requirement = $request->requirement;
        $course->film = $request->film;
        $course->duration = $request->duration;
        $course->website = $request->website;
        $course->day = $request->day;
        $course->certification_id = $request->certification;
        $course->certifier_id = $request->certifier;
        $course->categorie_id = $request->categorie;
        $course->certificate = $request->certificate;
        $course->featured = $request->featured;
        $course->level = $request->level;
        $course->rating = $request->rating ?? 0;
        $course->available = $request->available;
        $course->payment = $request->payment;
        $course->exam = $request->exam;
        $course->promotion = $request->promotion;
        $course->save();

        return response()->json([
            'success' => true,
            'slack' => $course->slack,
            'message' => 'Se creo el curso correctamente',
        ]);

    }

    public function getThumbnails($slack)
    {

        $course = Course::slack($slack);

        if ($course->getMedia('thumbnail')->count() > 0) {

            $thumbnails = $course->getMedia('thumbnail');

            foreach ($thumbnails as $thumbnail) {

                $images[] = [
                    'id' => $thumbnail->id,
                    'uuid' => $thumbnail->uuid,
                    'name' => $thumbnail->name,
                    'file' => $thumbnail->file_name,
                    'path' => $thumbnail->getfullUrl(),
                    'size' => $thumbnail->size,
                ];
            }

            return response()->json($images);
        }

        $images = [];

        return response()->json($images);

    }

    public function storeThumbnails(StoreThumbnailRequest $request)
    {
        if (! $request->hasFile('file') || ! $request->file('file')->isValid()) {
            return response()->json(['status' => 'error', 'message' => 'El archivo no es válido.'], 422);
        }

        $course = Course::slack(Str::remove('"', $request->course));
        $course->addMediaFromRequest('file')->toMediaCollection('thumbnail');

        return response()->json(['status' => 'success', 'course' => $course->slack]);
    }

    public function deleteThumbnails($id)
    {
        Media::find($id)->delete();

        return response()->json(['status' => 'success']);
    }

    public function destroy($slack)
    {
        $course = Course::slack($slack);
        $course->delete();

        return redirect()->route('manager.courses');
    }

    private function duplicateFile(string $directory, ?string $filename): ?string
    {
        if (! $filename) {
            return null;
        }

        $path = public_path("{$directory}/{$filename}");

        if (! @file_get_contents($path)) {
            return null;
        }

        $extension = \File::extension($path);
        $newName = 'duplicate'.time().'.'.$extension;
        \File::copy($path, public_path("{$directory}/{$newName}"));

        return $newName;
    }
}
