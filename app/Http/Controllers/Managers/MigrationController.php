<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Model\Enterprise as EnterpriseSecond;
use App\Model\Order as OrderSecond;
use App\Model\User as UserSecond;
use App\Models\Course\Course;
use App\Models\Course\CourseProgress;
use App\Models\Enterprise\Enterprise as EnterprisePrimary;
use App\Models\Enterprise\EnterpriseCourse as EnterpriseCoursePrimary;
use App\Models\Enterprise\EnterpriseUser as EnterpriseUserPrimary;
use App\Models\Exam\Exam;
use App\Models\Exam\ExamAnswer;
use App\Models\Inscription;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Quiz\Quiz;
use App\Models\Quiz\QuizAnswer;
use App\Models\User as UserPrimary;
use App\Models\Users\Certificate;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MigrationController extends Controller
{
    public function users2(Request $request)
    {

        $users = UserSecond::where('migrate', 0)->get();

        foreach ($users as $user) {

            $useritem = new UserPrimary;
            $useritem->id = $user->id;
            $useritem->slack = $user->slack;
            $useritem->firstname = $user->firstname;
            $useritem->lastname = $user->lastname;
            $useritem->cellphone = $user->cellphone;
            $useritem->identification = $user->identification;
            $useritem->email = $user->email;
            $useritem->address = $user->address;
            $useritem->company = $user->company;
            $useritem->detail = $user->detail;
            $useritem->role = $user->role;
            $useritem->password = $user->password;
            $useritem->available = $user->available;
            $useritem->verified = $user->verified;
            $useritem->validation = $user->validation;
            $useritem->terms = $user->terms;
            $useritem->page = $user->page;
            $useritem->setting = $user->setting;
            $useritem->email_verified_at = $user->email_verified_at;
            $useritem->remember_token = $user->remember_token;
            $useritem->citie_id = $user->citie_id;
            $useritem->enterprise_id = $user->enterprise_id;
            $useritem->created_at = $user->created_at;
            $useritem->updated_at = $user->updated_at;
            $useritem->save();

            $user->migrate = 1;
            $user->save();
        }

        \Log::info('validate users2 migration finished');

    }

    public function users(Request $request)
    {

        $users = UserSecond::where('migrate', 1)->get();

        foreach ($users as $user) {

            $useritem = UserPrimary::id($user->id);
            $useritem->password = $user->password;
            $useritem->save();

            $user->migrate = 0;
            $user->save();
        }

        \Log::info('validate users migration finished');

    }

    public function enterprises()
    {

        $enterprises = EnterpriseSecond::get();

        foreach ($enterprises as $enterprise) {

            $enterpriseitem = new EnterprisePrimary;
            $enterpriseitem->id = $enterprise->id;
            $enterpriseitem->slack = $this->generate_slack('enterprises');
            $enterpriseitem->title = $enterprise->title;
            $enterpriseitem->slug = $enterprise->slug;
            $enterpriseitem->address = $enterprise->address;
            $enterpriseitem->cellphone = $enterprise->cellphone;
            $enterpriseitem->nit = $enterprise->nit;
            $enterpriseitem->email = $enterprise->email;
            $enterpriseitem->available = $enterprise->available;
            $enterpriseitem->leading = null;
            $enterpriseitem->supporting = null;
            $enterpriseitem->mail_notification = null;
            $enterpriseitem->inscription_notification = null;
            $enterpriseitem->created_at = $enterprise->created_at;
            $enterpriseitem->updated_at = $enterprise->updated_at;
            $enterpriseitem->save();
            \Log::info('enterprise migrated: '.$enterprise->title);

        }

        \Log::info('finish enterprises');
    }

    public function userenterprises()
    {

        $enterprises = EnterpriseSecond::get();

        foreach ($enterprises as $enterprise) {

            \Log::info('userenterprise enterprise: '.$enterprise->title);
            $users = $enterprise->users;

            foreach ($users as $user) {
                $useritem = new EnterpriseUserPrimary;
                $useritem->id = $user->id;
                $useritem->user_id = $user->user_id;
                $useritem->enterprise_id = $user->enterprise_id;
                $useritem->available = $user->available;
                $useritem->created_at = $user->created_at;
                $useritem->updated_at = $user->updated_at;
                $useritem->save();
                \Log::info('userenterprise user migrated id: '.$useritem->id);
            }

        }

        \Log::info('finish userenterprises');
    }

    public function coursesenterprises()
    {

        $enterprises = EnterpriseSecond::get();

        foreach ($enterprises as $enterprise) {

            \Log::info('coursesenterprise enterprise: '.$enterprise->title);
            $courses = $enterprise->courses;

            foreach ($courses as $course) {
                $courseitem = new EnterpriseCoursePrimary;
                $courseitem->id = $course->id;
                $courseitem->course_id = $course->course_id;
                $courseitem->enterprise_id = $course->enterprise_id;
                $courseitem->price = 0;
                $courseitem->created_at = $course->created_at;
                $courseitem->updated_at = $course->updated_at;
                $courseitem->save();
                \Log::info('coursesenterprise course migrated id: '.$courseitem->id);
            }

        }

        \Log::info('finish coursesenterprises');
    }

    public function orders(Request $request)
    {

        $orders = OrderSecond::where('migrate', 0)->get();

        // foreach ($orders as $item) {
        //    $item->validate=0;
        //    $item->save();
        // }

        $number = 1;

        foreach ($orders as $item) {

            $coursing = $item->coursing;

            if (empty($coursing) == false) {

                $order = new Order;
                $order->id = $item->id;
                $order->slack = $item->slack;
                $order->number = $item->id;
                $order->reference = 'FAC'.$item->id;
                $order->user_id = $item->user_id;
                $order->type_id = $item->method_id;
                $order->method_id = $item->method_id;
                $order->condition_id = $item->condition_id;
                $order->coupon_id = null;
                $order->transaction = $item->transaction;
                $order->notes = null;
                $order->payment_at = $item->payment_at == null ? $item->created_at : $item->payment_at;
                $order->total_discount_amount = $item->discount;
                $order->total_after_discount = $item->subtotal;
                $order->total_before_discount = $item->subtotal;
                $order->total_tax_amount = 0;
                $order->total_order_amount = $item->total;
                $order->created_at = $item->created_at;
                $order->updated_at = $item->updated_at;
                $order->save();

                $orderitem = new OrderItem;
                $orderitem->slack = $this->generate_slack('order_items');
                $orderitem->order_id = $order->id;
                $orderitem->item_id = $item->course_id;
                $orderitem->item_type = Course::class;
                $orderitem->quantity = 1;
                $orderitem->amount = $item->total;
                $orderitem->created_at = $item->created_at;
                $orderitem->updated_at = $item->updated_at;
                $orderitem->save();

                $enroll = Carbon::now();
                $expire = Carbon::parse($item->enroll_expire);

                $inscription = new Inscription;
                $inscription->slack = $this->generate_slack('inscriptions');
                $inscription->order_id = $order->id;
                $inscription->user_id = $item->user_id;
                $inscription->course_id = $item->course_id;
                $inscription->percent = $coursing->percent > 100 ? 100 : $coursing->percent;
                $inscription->enroll_start = $item->enroll_start;
                $inscription->enroll_expire = $item->enroll_expire;
                $inscription->enroll_culminated = $coursing->culminated_at;
                $inscription->culminated = $coursing->culminated;
                $inscription->created_at = $coursing->created_at;
                $inscription->updated_at = $coursing->updated_at;

                if ($enroll->diffInDays($expire, false) < 0) {
                    $inscription->expire = 1;
                } else {
                    $inscription->expire = 0;
                }

                $inscription->save();

                if (empty($item->exam) == false) {

                    $exams = $item->exam;

                    $exam = new Exam;
                    $exam->course_id = $exams->course_id;
                    $exam->user_id = $exams->user_id;
                    $exam->inscription_id = $inscription->id;
                    $exam->topic_id = $exams->topic_id;
                    $exam->correct = $exams->correct;
                    $exam->wrong = $exams->wrong;
                    $exam->score = $exams->score;
                    $exam->created_at = $exams->created_at;
                    $exam->updated_at = $exams->updated_at;
                    $exam->save();

                    if (count($exams->answers) > 0) {
                        $answers = $exams->answers;
                        foreach ($answers as $itemsanswer) {
                            $answer = new ExamAnswer;
                            $answer->exam_id = $exam->id;
                            $answer->course_id = $itemsanswer->course_id;
                            $answer->topic_id = $itemsanswer->topic_id;
                            $answer->user_id = $itemsanswer->user_id;
                            $answer->question_id = $itemsanswer->question_id;
                            $answer->user_answer = $itemsanswer->user_answer;
                            $answer->answer = $itemsanswer->answer;
                            $answer->type = $itemsanswer->type;
                            $answer->approved = $itemsanswer->approved;
                            $answer->created_at = $itemsanswer->created_at;
                            $answer->updated_at = $itemsanswer->updated_at;
                            $answer->save();
                        }
                    }
                }

                if (empty($item->certificate) == false) {
                    $certificates = new Certificate;
                    $certificates->slack = $this->generate_slack('certificates');
                    $certificates->user_id = $item->certificate->user_id;
                    $certificates->course_id = $item->certificate->course_id;
                    $certificates->inscription_id = $inscription->id;
                    $certificates->exam_id = $exam->id;
                    $certificates->certification_id = $item->certificate->certification_id;
                    $certificates->certifier_id = $item->certificate->certifier_id;
                    $certificates->start_at = $item->certificate->start_at;
                    $certificates->end_at = $item->certificate->end_at;
                    $certificates->created_at = $item->certificate->created_at;
                    $certificates->updated_at = $item->certificate->updated_at;
                    $certificates->save();
                }

                if (count($item->quizs) > 0) {
                    foreach ($item->quizs as $itemsquiz) {

                        $quiz = new Quiz;
                        $quiz->lesson_id = $itemsquiz->class_id;
                        $quiz->course_id = $itemsquiz->course_id;
                        $quiz->user_id = $itemsquiz->user_id;
                        $quiz->topic_id = $itemsquiz->topic_id;
                        $quiz->inscription_id = $inscription->id;
                        $quiz->correct = $itemsquiz->correct;
                        $quiz->wrong = $itemsquiz->wrong;
                        $quiz->score = $itemsquiz->score;
                        $quiz->created_at = $itemsquiz->created_at;
                        $quiz->updated_at = $itemsquiz->updated_at;
                        $quiz->save();

                        if (count($itemsquiz->answers) > 0) {
                            $answers = $itemsquiz->answers;
                            foreach ($answers as $answeritem) {
                                $answer = new QuizAnswer;
                                $answer->quiz_id = $quiz->id;
                                $answer->lesson_id = $answeritem->class_id;
                                $answer->topic_id = $answeritem->topic_id;
                                $answer->user_id = $answeritem->user_id;
                                $answer->question_id = $answeritem->question_id;
                                $answer->user_answer = $answeritem->user_answer;
                                $answer->answer = $answeritem->answer;
                                $answer->type = $answeritem->type;
                                $answer->approved = $answeritem->approved;
                                $answer->created_at = $answeritem->created_at;
                                $answer->updated_at = $answeritem->updated_at;
                                $answer->save();
                            }
                        }

                    }
                }

                if (count($item->progress) > 0) {

                    foreach ($item->progress as $progressitem) {
                        $progresst = new CourseProgress;
                        $progresst->user_id = $progressitem->user_id;
                        $progresst->course_id = $progressitem->course_id;
                        $progresst->chapter_id = $progressitem->chapter_id;
                        $progresst->lesson_id = $progressitem->lesson_id;
                        $progresst->inscription_id = $inscription->id;
                        $progresst->culminated = $progressitem->culminated;
                        $progresst->created_at = $progressitem->created_at;
                        $progresst->updated_at = $progressitem->updated_at;
                        $progresst->save();
                    }

                }

                $item->migrate = 1;
                $item->save();

            } else {

                $order = new Order;
                $order->id = $item->id;
                $order->slack = $item->slack;
                $order->number = $item->id;
                $order->reference = 'FAC'.$item->id;
                $order->user_id = $item->user_id;
                $order->type_id = $item->method_id;
                $order->method_id = $item->method_id;
                $order->condition_id = $item->condition_id;
                $order->coupon_id = null;
                $order->transaction = $item->transaction;
                $order->notes = null;
                $order->payment_at = $item->payment_at == null ? $item->created_at : $item->payment_at;
                $order->total_discount_amount = $item->discount;
                $order->total_after_discount = $item->subtotal;
                $order->total_before_discount = $item->subtotal;
                $order->total_tax_amount = 0;
                $order->total_order_amount = $item->total;
                $order->created_at = $item->created_at;
                $order->updated_at = $item->updated_at;
                $order->save();

                $orderitem = new OrderItem;
                $orderitem->slack = $this->generate_slack('order_items');
                $orderitem->order_id = $order->id;
                $orderitem->item_id = $item->course_id;
                $orderitem->item_type = Course::class;
                $orderitem->quantity = 1;
                $orderitem->amount = $item->total;
                $orderitem->created_at = $item->created_at;
                $orderitem->updated_at = $item->updated_at;
                $orderitem->save();

                $enroll = Carbon::now();
                $expire = Carbon::parse($item->enroll_expire);

                $inscription = new Inscription;
                $inscription->slack = $this->generate_slack('inscriptions');
                $inscription->order_id = $order->id;
                $inscription->user_id = $item->user_id;
                $inscription->course_id = $item->course_id;
                $inscription->percent = 0;
                $inscription->enroll_start = $item->enroll_start;
                $inscription->enroll_expire = $item->enroll_expire;
                $inscription->culminated = 0;
                $inscription->created_at = $item->created_at;
                $inscription->updated_at = $item->created_at;

                if ($enroll->diffInDays($expire, false) < 0) {
                    $inscription->expire = 1;
                } else {
                    $inscription->expire = 0;
                }

                $inscription->save();

            }

            $number++;

            $item->migrate = 1;
            $item->save();

        }

        \Log::info('finish orders');

    }

    public function coursesprogress(Request $request)
    {

        $orders = OrderSecond::where('migrate', 0)->get();

        $number = 1;

        foreach ($orders as $item) {

            $coursing = $item->coursing;

            if (empty($coursing) == false) {

                $order = Order::slack($item->slack);

                $inscription = $order->inscription;

                if (count($item->progress) > 0) {
                    foreach ($item->progress as $progressitem) {

                        $existingProgress = CourseProgress::where('id', $progressitem->id)->first();

                        if (! $existingProgress) {
                            $progresst = new CourseProgress;
                            $progresst->id = $progressitem->id;
                            $progresst->user_id = $progressitem->user_id;
                            $progresst->course_id = $progressitem->course_id;
                            $progresst->chapter_id = $progressitem->chapter_id;
                            $progresst->lesson_id = $progressitem->class_id;
                            $progresst->inscription_id = $inscription->id;
                            $progresst->culminated = $progressitem->culminated;
                            $progresst->created_at = $progressitem->created_at;
                            $progresst->updated_at = $progressitem->updated_at;
                            $progresst->save();
                        }
                    }

                }

                $item->migrate = 1;
                $item->save();

            }

        }

        \Log::info('finish coursesprogress');

    }
}
