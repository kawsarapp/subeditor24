<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class UserScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (!Auth::check()) return;

        $user = Auth::user();
        $table = $model->getTable();

        if ($user->role === 'super_admin') {
            // Super admin সব দেখবে
            return;
        }

        if (in_array($user->role, ['staff', 'reporter'])) {
            if ($table === 'websites') {
                // Staff: admin-এর websites, assigned websites, অথবা platform global websites
                $builder->where(function ($q) use ($user) {
                    $q->where('user_id', $user->parent_id)
                      ->orWhereNull('user_id')
                      ->orWhereHas('user', function ($sq) {
                          $sq->where('role', 'super_admin');
                      })
                      ->orWhereHas('users', function ($q2) use ($user) {
                          $q2->where('users.id', $user->id);
                      });
                });
            } else {
                // অন্য সব table (news_items etc): admin-এর pool বা নিজের staff_id
                $builder->where(function ($q) use ($table, $user) {
                    $q->where($table . '.user_id', $user->parent_id)
                      ->orWhere($table . '.staff_id', $user->id);
                });
            }
        } else {
            if ($table === 'websites') {
                // Websites table: Client Admin sees their own websites + global websites (created by super admin or null) + pivot assigned
                $builder->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhereNull('user_id')
                      ->orWhereHas('user', function ($sq) {
                          $sq->where('role', 'super_admin');
                      })
                      ->orWhereHas('users', function ($q2) use ($user) {
                          $q2->where('users.id', $user->id);
                      });
                });
            } else {
                // news_items etc: STRICT TENANT ISOLATION (নিজের user_id এর ডাটা)
                $builder->where($table . '.user_id', $user->id);
            }
        }
    }
}