<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Category $category): bool
    {
        return $category->is_system || $user->id === $category->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, Category $category): bool
    {
        // System categories can only be updated by admins
        if ($category->is_system) {
            return $user->isAdmin();
        }

        return $user->id === $category->user_id;
    }

    public function delete(User $user, Category $category): bool
    {
        // Users cannot delete system categories
        if ($category->is_system) {
            return false;
        }

        return $user->id === $category->user_id;
    }
}
