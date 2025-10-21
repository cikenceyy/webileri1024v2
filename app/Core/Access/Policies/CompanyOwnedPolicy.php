<?php

namespace App\Core\Access\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class CompanyOwnedPolicy
{
    protected string $permissionPrefix = '';

    protected function allows(User $user, Model $model, string $suffix): bool
    {
        if (! $this->belongsToCompany($user, $model)) {
            return false;
        }

        $permission = $this->permissionKey($suffix);

        return $permission === '' || $user->can($permission);
    }

    protected function belongsToCompany(User $user, Model $model): bool
    {
        return (int) $user->company_id === (int) ($model->company_id ?? 0);
    }

    protected function permissionKey(string $suffix): string
    {
        $base = trim($this->permissionPrefix);
        $suffix = trim($suffix);

        if ($base === '') {
            return $suffix;
        }

        if ($suffix === '') {
            return $base;
        }

        return $base . '.' . $suffix;
    }

    public function view(User $user, Model $model): bool
    {
        return $this->allows($user, $model, 'view');
    }

    public function create(User $user): bool
    {
        return $this->permissionPrefix === ''
            ? $user->can('create')
            : $user->can($this->permissionKey('create'));
    }

    public function update(User $user, Model $model): bool
    {
        return $this->allows($user, $model, 'update');
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->allows($user, $model, 'delete');
    }
}
