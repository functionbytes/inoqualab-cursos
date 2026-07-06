<?php

namespace App\Mail\Customers\Courses;

use App\Models\Course\Course;
use App\Models\Inscription;
use App\Services\MailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Remarketing post-completación (cross-sell). Se envía al alumno cuando termina
 * un curso: lo felicita y le recomienda otros cursos que aún no ha adquirido,
 * para incentivar una nueva compra. Sigue el mismo patrón que RenewalMail:
 * plantilla editable en el panel (mail_templates) renderizada por MailTemplateService.
 */
class CompletedCourseMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $firstname;

    public string $toEmail;

    public string $courseTitle;

    public string $catalogUrl;

    public string $recommendedCoursesHtml;

    public function __construct(Inscription $inscription)
    {
        $this->firstname = $inscription->user->firstname ?? '';
        $this->toEmail = $inscription->user->email ?? '';
        $this->courseTitle = $inscription->course->title ?? 'tu curso';
        $this->catalogUrl = route('courses');
        $this->recommendedCoursesHtml = $this->buildRecommendedCourses($inscription);
    }

    public function build(): self
    {
        $data = app(MailTemplateService::class)->render('inscriptions.completed', [
            'CUSTOMER_FIRSTNAME' => $this->firstname,
            'COURSE_TITLE' => $this->courseTitle,
            'CATALOG_URL' => $this->catalogUrl,
            'RECOMMENDED_COURSES' => $this->recommendedCoursesHtml,
        ]);

        return $this->to($this->toEmail)
            ->subject($data['subject'])
            ->html($data['html']);
    }

    /**
     * Bloque HTML con hasta 3 cursos disponibles que el alumno NO posee todavía
     * (prioriza destacados). Se inyecta como una sola variable de plantilla.
     */
    private function buildRecommendedCourses(Inscription $inscription): string
    {
        $ownedCourseIds = Inscription::where('user_id', $inscription->user_id)
            ->pluck('course_id')
            ->all();

        $recommended = Course::query()
            ->available()
            ->whereNotIn('id', $ownedCourseIds)
            ->orderByDesc('featured')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        if ($recommended->isEmpty()) {
            return '';
        }

        $rows = '';
        foreach ($recommended as $course) {
            $title = htmlspecialchars((string) $course->title, ENT_QUOTES, 'UTF-8');
            $price = '$'.number_format((float) $course->price, 0, ',', '.');
            $url = route('checkout', ['course', $course->slack]);

            $rows .= <<<HTML
<tr><td style="padding:0 0 12px;">
  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border:1px solid #ECECEC;border-radius:6px;">
    <tr>
      <td style="padding:14px 16px;">
        <p style="color:#081A28;font-size:14px;font-weight:600;margin:0 0 4px;">{$title}</p>
        <p style="color:#008bce;font-size:14px;font-weight:700;margin:0;">{$price}</p>
      </td>
      <td style="padding:14px 16px;text-align:right;" align="right">
        <a href="{$url}" style="background-color:#081A28;color:#FFFFFF;font-size:12px;font-weight:600;letter-spacing:.5px;text-decoration:none;text-transform:uppercase;padding:9px 18px;border-radius:4px;display:inline-block;">
          Ver curso
        </a>
      </td>
    </tr>
  </table>
</td></tr>
HTML;
        }

        return '<table border="0" cellpadding="0" cellspacing="0" width="100%">'.$rows.'</table>';
    }
}
