<?php

namespace App\Modules\_template\Policies;

use App\Models\User;

/**
 * Modül bazlı yetki kurallarını tanımlamak için policy şablonu.
 * Yöntemleri gerçek iş kurallarınıza göre doldurun.
 */
class ExamplePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage example module');
    }

    public function view(User $user, object $record): bool
    {
        return $user->can('manage example module');
    }

    public function create(User $user): bool
    {
        return $user->can('manage example module');
    }

    public function update(User $user, object $record): bool
    {
        return $user->can('manage example module');
    }

    public function delete(User $user, object $record): bool
    {
        return $user->can('manage example module');
    }
}
