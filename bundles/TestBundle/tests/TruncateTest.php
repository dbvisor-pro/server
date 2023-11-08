<?php

namespace DbManager\TestBundle\Tests;

use Doctrine\DBAL\Query\QueryBuilder;

class TruncateTest extends AbstractTest
{
    public function testNotificationTable(): void
    {
        $queryBuilder = new QueryBuilder($this->connection);
        $queryBuilder
            ->select('*')
            ->from('user_notifications');

        $statement = $this->connection->executeQuery($queryBuilder->getSQL());

        // Fetch all rows from the result
        $rows = $statement->fetchAllAssociative();

        $this->assertEmpty($rows);
    }
}
