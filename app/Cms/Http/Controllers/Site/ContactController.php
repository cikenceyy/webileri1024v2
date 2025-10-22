<?php

namespace App\Cms\Http\Controllers\Site;

use App\Cms\Mail\ContactMessageSubmitted;
use App\Cms\Models\ContactMessage;
use App\Cms\Support\CmsRepository;
use App\Cms\Support\Seo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    public function __construct(protected CmsRepository $repository, protected Seo $seo)
    {
    }

    public function index()
    {
        return $this->render('tr');
    }

    public function indexEn()
    {
        return $this->render('en');
    }

    protected function render(string $locale)
    {
        $data = $this->repository->read('contact', $locale);
        $seo = $this->seo->for('contact', [], $locale);

        return view('cms::site.contact', [
            'locale' => $locale,
            'data' => $data,
            'seo' => $seo,
            'scripts' => $this->repository->scripts('contact', $locale),
        ]);
    }

    public function submit(Request $request)
    {
        $locale = $request->segment(1) === 'en' ? 'en' : 'tr';

        if ($request->filled('website')) {
            return redirect()->back()->withErrors(['message' => __('Invalid submission detected.')]);
        }

        if (!$this->passesDelay($request->input('submitted_at'))) {
            return redirect()->back()->withErrors(['message' => __('Please wait a little longer before submitting.')]);
        }

        $key = 'cms-contact:' . $this->repository->companyId() . ':' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return redirect()->back()->withErrors(['message' => __('You are submitting too fast. Please try again later.')]);
        }

        RateLimiter::hit($key, 60);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $record = ContactMessage::create([
            'company_id' => $this->repository->companyId(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'is_read' => false,
        ]);

        $emails = $this->repository->emails();
        $notify = $emails['notify_email'] ?? null;

        if ($notify) {
            $mailable = new ContactMessageSubmitted($record);
            $message = Mail::to($notify);
            if (!empty($emails['info_email'])) {
                $message->cc($emails['info_email']);
            }
            $message->queue($mailable);
        }

        return redirect()->back()->with('status', __('Thank you! We will get back to you soon.'));
    }

    protected function passesDelay(?string $timestamp): bool
    {
        if (!$timestamp) {
            return false;
        }

        $submitted = Carbon::createFromTimestamp((int) $timestamp);

        return now()->diffInSeconds($submitted) >= 2;
    }
}
