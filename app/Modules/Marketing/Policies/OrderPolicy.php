<?php

namespace App\Modules\Marketing\Policies;

use App\Core\Bus\Listeners\EnsureCustomerCreditLimit;
use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\Inventory\Domain\Models\StockMovement;
use App\Modules\Marketing\Domain\Models\Order;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderPolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'marketing.order';

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionKey('view'));
    }

    public function view(User $user, Order $order): bool
    {
        return parent::view($user, $order);
    }

    public function create(User $user): bool
    {
        return parent::create($user);
    }

    public function update(User $user, Order $order): bool
    {
        return parent::update($user, $order);
    }

    public function delete(User $user, Order $order): bool
    {
        return parent::delete($user, $order);
    }

    public function approve(User $user, Order $order): Response
    {
        if (! $this->belongsToCompany($user, $order)) {
            return Response::deny(__('You cannot approve orders for another company.'));
        }

        if (! $user->can($this->permissionKey('approve'))) {
            return Response::deny(__('You are not allowed to approve orders.'));
        }

        $order->loadMissing(['customer', 'lines']);

        try {
            EnsureCustomerCreditLimit::assertWithinLimit($order);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors()['credit_limit'] ?? [])->first();

            return Response::deny($message ?: $exception->getMessage());
        }

        if ($violation = $this->validatePaymentTerms($order)) {
            return $violation;
        }

        if ($violation = $this->validateMargin($order)) {
            return $violation;
        }

        return Response::allow();
    }

    protected function validatePaymentTerms(Order $order): ?Response
    {
        $customer = $order->customer;

        if (! $customer) {
            return null;
        }

        $days = $this->extractPaymentTermDays($customer->payment_terms ?? null);

        if (! $days) {
            return null;
        }

        $orderDate = $order->order_date;
        $dueDate = $order->due_date;

        if (! $orderDate || ! $dueDate) {
            return null;
        }

        $maxDue = $orderDate->copy()->addDays($days);

        if ($dueDate->greaterThan($maxDue)) {
            return Response::deny(__('Due date exceeds the customer payment terms of :days days.', ['days' => $days]));
        }

        return null;
    }

    protected function validateMargin(Order $order): ?Response
    {
        $config = config('marketing.module');
        $minMargin = (float) ($config['approvals']['min_margin_percent'] ?? env('CRM_MIN_MARGIN_PERCENT', 0));

        if ($minMargin <= 0) {
            return null;
        }

        $lines = $order->lines instanceof Collection ? $order->lines : collect($order->lines);

        if ($lines->isEmpty()) {
            return null;
        }

        $averages = $this->averageCosts($order, $lines);

        foreach ($lines as $index => $line) {
            $qty = (float) ($line->qty ?? 0);
            $lineTotal = (float) ($line->line_total ?? 0);

            if ($qty <= 0 || $lineTotal <= 0) {
                continue;
            }

            $netPerUnit = $lineTotal / $qty;
            $avgCost = $averages[$this->costKey($line->product_id, $line->variant_id)] ?? null;

            if ($avgCost === null || $avgCost <= 0) {
                continue;
            }

            $marginPercent = $netPerUnit <= 0 ? -100 : (($netPerUnit - $avgCost) / $netPerUnit) * 100;

            if ($marginPercent + 1e-6 < $minMargin) {
                $label = Str::of($line->description ?? '')->trim()->value() ?: __('Line :number', ['number' => $index + 1]);

                return Response::deny(__('Margin for :item falls below the minimum :percent% requirement.', [
                    'item' => $label,
                    'percent' => number_format($minMargin, 1),
                ]));
            }
        }

        return null;
    }

    protected function extractPaymentTermDays(?string $terms): ?int
    {
        if ($terms === null || trim($terms) === '') {
            return null;
        }

        if (is_numeric($terms)) {
            $days = (int) $terms;

            return $days > 0 ? $days : null;
        }

        if (preg_match('/(\d+)/', $terms, $matches)) {
            $days = (int) ($matches[1] ?? 0);

            return $days > 0 ? $days : null;
        }

        return null;
    }

    /**
     * @param  Collection<int, \App\Modules\Marketing\Domain\Models\OrderLine>  $lines
     * @return array<string, float|null>
     */
    protected function averageCosts(Order $order, Collection $lines): array
    {
        $productIds = $lines->pluck('product_id')->filter()->unique()->all();

        if ($productIds === []) {
            return [];
        }

        $rows = StockMovement::query()
            ->selectRaw(
                "product_id, variant_id, " .
                "COALESCE(SUM(CASE WHEN direction = 'in' THEN qty ELSE -qty END), 0) as total_qty, " .
                "COALESCE(SUM(CASE WHEN direction = 'in' THEN qty * unit_cost ELSE 0 END), 0) as total_cost"
            )
            ->where('company_id', $order->company_id)
            ->whereIn('product_id', $productIds)
            ->groupBy('product_id', 'variant_id')
            ->get();

        $averages = [];

        foreach ($rows as $row) {
            $qty = (float) $row->total_qty;
            $cost = (float) $row->total_cost;
            $key = $this->costKey($row->product_id, $row->variant_id);

            $averages[$key] = $qty <= 0 ? null : $cost / $qty;
        }

        return $averages;
    }

    protected function costKey(?int $productId, ?int $variantId): string
    {
        return implode(':', [$productId ?: 'product', $variantId ?: 'variant']);
    }
}
