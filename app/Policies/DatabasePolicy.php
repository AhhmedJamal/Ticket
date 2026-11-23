<?php

namespace App\Policies;

use App\Models\NotionDatabase;
use App\Models\User;

class DatabasePolicy
{
    public function view(User $user, NotionDatabase $database): bool
    {
        return $database->page ? (new PagePolicy)->view($user, $database->page) : false;
    }

    public function update(User $user, NotionDatabase $database): bool
    {
        return $database->page ? (new PagePolicy)->update($user, $database->page) : false;
    }

    public function delete(User $user, NotionDatabase $database): bool
    {
        return $database->page ? (new PagePolicy)->update($user, $database->page) : false;
    }
}
