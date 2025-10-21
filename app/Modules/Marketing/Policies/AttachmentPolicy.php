<?php

namespace App\Modules\Marketing\Policies;

use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\Marketing\Domain\Models\Attachment;

class AttachmentPolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'marketing.attachment';

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionKey('view'));
    }

    public function view(User $user, Attachment $attachment): bool
    {
        return parent::view($user, $attachment);
    }

    public function create(User $user): bool
    {
        return parent::create($user);
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        return parent::delete($user, $attachment);
    }
}
