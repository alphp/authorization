<?php
declare(strict_types=1);

namespace TestPlugin\Policy;

use Authorization\IdentityInterface;
use Cake\ORM\Query\SelectQuery;

/**
 * Users policy
 */
class UsersTablePolicy
{
    /**
     * Apply user access controls to a query for index actions
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \Cake\ORM\Query\SelectQuery $query The query to apply authorization conditions to.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function scopeIndex(IdentityInterface $user, SelectQuery $query): SelectQuery
    {
    }
}
