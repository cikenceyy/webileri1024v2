<?php

namespace App\Modules\Marketing\Policies;

use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\Marketing\Domain\Models\Note;

class NotePolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'marketing.note';

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionKey('view'));
    }

    public function view(User $user, Note $note): bool
    {
        return parent::view($user, $note);
    }

    public function create(User $user): bool
    {
        return parent::create($user);
    }

    public function delete(User $user, Note $note): bool
    {
        return parent::delete($user, $note);
    }
}
