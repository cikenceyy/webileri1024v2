<?php

namespace App\Modules\Drive\Policies;

use App\Modules\Drive\Domain\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'drive.view');
    }

    public function view(User $user, Media $media): bool
    {
        return $this->hasPermission($user, 'drive.view') && $this->ownsMedia($user, $media);
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'drive.upload');
    }

    public function delete(User $user, Media $media): bool
    {
        return $this->hasPermission($user, 'drive.delete') && $this->ownsMedia($user, $media);
    }

    public function replace(User $user, Media $media): bool
    {
        return $this->hasPermission($user, 'drive.replace') && $this->ownsMedia($user, $media);
    }

    public function markImportant(User $user, Media $media): bool
    {
        return $this->hasPermission($user, 'drive.mark_important') && $this->ownsMedia($user, $media);
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (
            class_exists(\Spatie\Permission\PermissionRegistrar::class)
            && method_exists($user, 'hasPermissionTo')
        ) {
            return $user->hasPermissionTo($permission);
        }

        $companyId = app()->bound('company') ? (int) (app('company')->id ?? 0) : 0;

        return (int) $user->company_id === $companyId;
    }

    protected function ownsMedia(User $user, Media $media): bool
    {
        return (int) $user->company_id === (int) $media->company_id;
    }
}
