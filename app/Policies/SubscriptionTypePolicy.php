<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SubscriptionType;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubscriptionTypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SubscriptionType');
    }

    public function view(AuthUser $authUser, SubscriptionType $subscriptionType): bool
    {
        return $authUser->can('View:SubscriptionType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SubscriptionType');
    }

    public function update(AuthUser $authUser, SubscriptionType $subscriptionType): bool
    {
        return $authUser->can('Update:SubscriptionType');
    }

    public function delete(AuthUser $authUser, SubscriptionType $subscriptionType): bool
    {
        return $authUser->can('Delete:SubscriptionType');
    }

    public function restore(AuthUser $authUser, SubscriptionType $subscriptionType): bool
    {
        return $authUser->can('Restore:SubscriptionType');
    }

    public function forceDelete(AuthUser $authUser, SubscriptionType $subscriptionType): bool
    {
        return $authUser->can('ForceDelete:SubscriptionType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SubscriptionType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SubscriptionType');
    }

    public function replicate(AuthUser $authUser, SubscriptionType $subscriptionType): bool
    {
        return $authUser->can('Replicate:SubscriptionType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SubscriptionType');
    }

}