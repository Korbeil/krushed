<?php

namespace Krushed\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Krushed\Entity\Command;
use Symfony\Component\Cache\Adapter\AdapterInterface;

/**
 * @method Command|null find($id, $lockMode = null, $lockVersion = null)
 * @method Command|null findOneBy(array $criteria, array $orderBy = null)
 * @method Command[]    findAll()
 * @method Command[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandRepository extends ServiceEntityRepository
{
    private const CACHE_TTL = 60 * 60 * 24; // 1 day cache

    private AdapterInterface $cache;

    public function __construct(ManagerRegistry $registry, AdapterInterface $adapter)
    {
        parent::__construct($registry, Command::class);
        $this->cache = $adapter;
    }

    public function getOutputByCommandNameForDiscord(string $name): ?string
    {
        return $this->getOutputByCommandName(sprintf('discord_%s', $name), Command::ENABLED_DISCORD);
    }

    public function getOutputByCommandNameForTwitch(string $name): ?string
    {
        return $this->getOutputByCommandName(sprintf('discord_%s', $name), Command::ENABLED_TWITCH);
    }

    private function getOutputByCommandName(string $name, int $flag): ?string
    {
        $cacheItem = $this->cache->getItem(sprintf('command_%s', $name));

        if (!$cacheItem->isHit()) {
            $command = $this->findOneBy(['name' => $name]);
            if ($command instanceof Command && $command->isEnabled($flag)) {
                $cacheItem->set($command->getOutput());
            } else {
                $cacheItem->set(null);
            }
            $cacheItem->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cacheItem);
        }

        return $cacheItem->get();
    }
}
