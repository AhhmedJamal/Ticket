<?php

namespace App\Policies;

use App\Models\Block;
use App\Models\User;

class BlockPolicy
{
    public function view(User $user, Block $block): bool
    {
        return $block->page ? (new PagePolicy)->view($user, $block->page) : false;
    }

    public function update(User $user, Block $block): bool
    {
        return $block->page ? (new PagePolicy)->update($user, $block->page) : false;
    }

    public function comment(User $user, Block $block): bool
    {
        return $block->page ? (new PagePolicy)->comment($user, $block->page) : false;
    }

    public function delete(User $user, Block $block): bool
    {
        return $block->page ? (new PagePolicy)->update($user, $block->page) : false;
    }
}
