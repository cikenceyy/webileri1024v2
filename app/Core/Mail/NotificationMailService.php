<?php

namespace App\Core\Mail;

use App\Core\Settings\Models\CompanyEmailLog;
use App\Core\Settings\SettingsRepository;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Tenant ayarlarına göre e-posta yönlendirmesini ve loglamasını üstlenir.
 */
class NotificationMailService
{
    public function __construct(private readonly SettingsRepository $settings)
    {
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function send(int $companyId, Mailable $mailable, array $context = []): void
    {
        $routing = $this->resolveRouting($companyId);

        $overrideTo = Arr::wrap($context['override_to'] ?? []);
        if ($overrideTo !== []) {
            $routing['to'] = array_values(array_unique(array_filter(array_map('strval', $overrideTo))));
            $routing['cc'] = [];
            $routing['bcc'] = [];
        }

        if (isset($context['override_reply_to'])) {
            $routing['reply_to'] = (string) $context['override_reply_to'];
        }

        if ($routing['to'] === []) {
            $this->log($companyId, 'skipped', $routing, array_merge($context, [
                'reason' => 'no_recipients',
            ]));

            Log::channel('notifications')->warning('E-posta gönderimi atlandı: alıcı bulunamadı.', [
                'company_id' => $companyId,
                'context' => $context,
            ]);

            return;
        }

        try {
            if (method_exists($mailable, 'onQueue')) {
                $mailable->onQueue('emails');
            }

            $message = Mail::to($routing['to']);

            if ($routing['cc'] !== []) {
                $message->cc($routing['cc']);
            }

            if ($routing['bcc'] !== []) {
                $message->bcc($routing['bcc']);
            }

            if ($routing['from']) {
                $message->from($routing['from']['address'], $routing['from']['name']);
            }

            if ($routing['reply_to']) {
                $message->replyTo($routing['reply_to']);
            }

            $message->queue($mailable);

            $this->log($companyId, 'sent', $routing, $context);

            Log::channel('notifications')->info('E-posta kuyruğa alındı.', [
                'company_id' => $companyId,
                'recipients' => $routing,
                'context' => $context,
            ]);
        } catch (Throwable $exception) {
            $this->log($companyId, 'failed', $routing, array_merge($context, [
                'error' => $exception->getMessage(),
            ]));

            Log::channel('notifications')->error('E-posta gönderimi başarısız.', [
                'company_id' => $companyId,
                'error' => $exception->getMessage(),
                'context' => $context,
            ]);
        }
    }

    /**
     * @return array{to: array<int, string>, cc: array<int, string>, bcc: array<int, string>, from: array{address: string, name: string}|null, reply_to: string|null}
     */
    private function resolveRouting(int $companyId): array
    {
        $x = (string) $this->settings->get($companyId, 'email.outbound.x', '');
        $y = (string) $this->settings->get($companyId, 'email.outbound.y', '');
        $deliverTo = (string) $this->settings->get($companyId, 'email.policy.deliver_to', 'both');
        $fromPolicy = (string) $this->settings->get($companyId, 'email.policy.from', 'system');
        $replyToPolicy = (string) $this->settings->get($companyId, 'email.policy.reply_to', 'x');
        $brandName = (string) $this->settings->get($companyId, 'email.brand.name', config('app.name'));
        $brandAddress = (string) $this->settings->get($companyId, 'email.brand.address', config('mail.from.address'));

        $to = [];
        $cc = [];

        if ($deliverTo === 'x_only' || $deliverTo === 'both') {
            if ($x !== '') {
                $to[] = $x;
            }
        }

        if ($deliverTo === 'y_only') {
            if ($y !== '') {
                $to[] = $y;
            }
        } elseif ($deliverTo === 'both' && $y !== '' && $y !== Arr::first($to)) {
            $cc[] = $y;
        }

        $from = null;

        if ($fromPolicy === 'x' && $x !== '') {
            $from = ['address' => $x, 'name' => $brandName ?: $x];
        } elseif ($fromPolicy === 'y' && $y !== '') {
            $from = ['address' => $y, 'name' => $brandName ?: $y];
        } elseif ($fromPolicy === 'system') {
            $from = ['address' => $brandAddress, 'name' => $brandName ?: config('app.name')];
        }

        $replyTo = null;
        if ($replyToPolicy === 'x' && $x !== '') {
            $replyTo = $x;
        } elseif ($replyToPolicy === 'y' && $y !== '') {
            $replyTo = $y;
        }

        $to = array_values(array_unique(array_filter($to)));
        $cc = array_values(array_unique(array_filter($cc)));

        if ($to === [] && $cc !== []) {
            $to[] = array_shift($cc);
        }

        return [
            'to' => $to,
            'cc' => $cc,
            'bcc' => [],
            'from' => $from,
            'reply_to' => $replyTo,
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function log(int $companyId, string $status, array $routing, array $meta = []): void
    {
        unset($meta['override_to'], $meta['override_reply_to']);

        CompanyEmailLog::query()->create([
            'company_id' => $companyId,
            'subject' => $meta['subject'] ?? null,
            'status' => $status,
            'recipients' => [
                'to' => $routing['to'],
                'cc' => $routing['cc'],
                'bcc' => $routing['bcc'],
                'from' => $routing['from'],
                'reply_to' => $routing['reply_to'],
            ],
            'meta' => $meta,
        ]);
    }
}
