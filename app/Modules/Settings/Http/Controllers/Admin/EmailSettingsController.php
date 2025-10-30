<?php

namespace App\Modules\Settings\Http\Controllers\Admin;

use App\Core\Mail\NotificationMailService;
use App\Core\Mail\TestNotificationMail;
use App\Core\Settings\Models\CompanyEmailLog;
use App\Core\Settings\SettingsRepository;
use App\Http\Controllers\Controller;
use App\Modules\Settings\Http\Requests\Admin\EmailSettingsRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * E-posta merkezi ekranını yönetir.
 */
class EmailSettingsController extends Controller
{
    public function __construct(
        private readonly SettingsRepository $settings,
        private readonly NotificationMailService $mailService,
    ) {
    }

    public function show(): View
    {
        $companyId = currentCompanyId();
        if (! $companyId) {
            throw new RuntimeException('Şirket kimliği çözümlenemedi.');
        }

        $values = $this->settings->many($companyId, [
            'email.outbound.x',
            'email.outbound.y',
            'email.policy.deliver_to',
            'email.policy.from',
            'email.policy.reply_to',
            'email.brand.name',
            'email.brand.address',
        ], [
            'email.policy.deliver_to' => 'both',
            'email.policy.from' => 'system',
            'email.policy.reply_to' => 'x',
        ]);

        $logs = CompanyEmailLog::query()
            ->where('company_id', $companyId)
            ->latest()
            ->limit(10)
            ->get();

        return view('settings::admin.email', [
            'values' => $values,
            'logs' => $logs,
        ]);
    }

    public function update(EmailSettingsRequest $request): JsonResponse
    {
        $companyId = currentCompanyId();
        if (! $companyId) {
            throw new RuntimeException('Şirket kimliği çözümlenemedi.');
        }

        $replyTo = $request->string('email_policy_reply_to')->toString();
        if ($replyTo === 'none') {
            $replyTo = '';
        }

        $this->settings->setMany($companyId, [
            'email.outbound.x' => ['value' => $request->input('email_outbound_x', ''), 'type' => 'email'],
            'email.outbound.y' => ['value' => $request->input('email_outbound_y', ''), 'type' => 'email'],
            'email.policy.deliver_to' => ['value' => $request->string('email_policy_deliver_to')->toString(), 'type' => 'string'],
            'email.policy.from' => ['value' => $request->string('email_policy_from')->toString(), 'type' => 'string'],
            'email.policy.reply_to' => ['value' => $replyTo, 'type' => 'string'],
            'email.brand.name' => ['value' => $request->input('email_brand_name', ''), 'type' => 'string'],
            'email.brand.address' => ['value' => $request->input('email_brand_address', ''), 'type' => 'string'],
        ], $request->user()?->getKey());

        return response()->json(['message' => 'E-posta ayarları güncellendi.']);
    }

    public function sendTest(): JsonResponse
    {
        $companyId = currentCompanyId();
        if (! $companyId) {
            throw new RuntimeException('Şirket kimliği çözümlenemedi.');
        }

        $user = request()->user();
        if (! $user) {
            throw new RuntimeException('Kullanıcı bulunamadı.');
        }

        $this->mailService->send($companyId, new TestNotificationMail($user->name ?? 'Yönetici'), [
            'subject' => 'Ayar Test E-postası',
            'channel' => 'settings.test',
            'override_to' => [$user->email],
            'triggered_by' => $user->email,
        ]);

        return response()->json(['message' => 'Deneme e-postası kuyruğa alındı.']);
    }
}
