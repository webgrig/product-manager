<?php

declare(strict_types=1);

namespace App\ReadModel\User;


use App\Model\User\Service\FetchModeService;
use Doctrine\DBAL\Connection;

class NetworkFetcher
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $userId
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getNotMembershipNetworks(string $userId)
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('network')
            ->distinct()
            ->from('user_user_networks')
            ->where('network NOT LIKE (SELECT network FROM user_user_networks WHERE user_id = :userId)')
            ->setParameter(':userId', $userId)
            ->execute();
        $result = $stmt->fetchAllAssociative();
        $result = $result ?: [];
        return FetchModeService::getNetworks(NetworkView::class, $result);
    }

    public function isUserHasNetwork(string $userId, string $networkName)
    {
        return $this->connection->createQueryBuilder()
                ->select('user_id')
                ->from('user_user_networks')
                ->where('user_id = :user_id')
                ->andWhere('network = :network')
                ->setParameter(':user_id', $userId)
                ->setParameter(':network', 'facebook')
                ->execute()
                ->rowCount();
    }
}
