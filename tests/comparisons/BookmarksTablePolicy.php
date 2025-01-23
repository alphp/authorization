<?php
declare(strict_types=1);

namespace TestApp\Policy;

use Authorization\IdentityInterface;
use Cake\ORM\Query\SelectQuery;

/**
 * Bookmarks policy
 */
class BookmarksTablePolicy
{
    /**
     * Apply user access controls to a query for index actions
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \Cake\ORM\Query\SelectQuery $query The query to apply authorization conditions to.
     * @return bool
     */
    public function scopeIndex(IdentityInterface $user, SelectQuery $query): SelectQuery
    {
    }
}
