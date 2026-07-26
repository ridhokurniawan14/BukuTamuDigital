<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Tamu;
use Illuminate\Auth\Access\HandlesAuthorization;

class TamuPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Tamu');
    }

    public function view(AuthUser $authUser, Tamu $tamu): bool
    {
        return $authUser->can('View:Tamu');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Tamu');
    }

    public function update(AuthUser $authUser, Tamu $tamu): bool
    {
        return $authUser->can('Update:Tamu');
    }

    public function delete(AuthUser $authUser, Tamu $tamu): bool
    {
        return $authUser->can('Delete:Tamu');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Tamu');
    }

    public function restore(AuthUser $authUser, Tamu $tamu): bool
    {
        return $authUser->can('Restore:Tamu');
    }

    public function forceDelete(AuthUser $authUser, Tamu $tamu): bool
    {
        return $authUser->can('ForceDelete:Tamu');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Tamu');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Tamu');
    }

    public function replicate(AuthUser $authUser, Tamu $tamu): bool
    {
        return $authUser->can('Replicate:Tamu');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Tamu');
    }

}