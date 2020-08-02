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

    public function getOutputByCommandNameForDiscord(string $name): ?array
    {
        return $this->getOutputByCommandName($name, 'discordEnabled');
    }

    public function getOutputByCommandNameForTwitch(string $name): ?array
    {
        return $this->getOutputByCommandName($name, 'twitchEnabled');
    }

    private function getOutputByCommandName(string $name, string $enabledKey): ?array
    {
        $commandKey = sprintf('command_%s', $name);
        $cacheItem = $this->cache->getItem($commandKey);

        if (!$cacheItem->isHit()) {
            $command = $this->findOneBy(['name' => $name]);
            if ($command instanceof Command) {
                $cacheItem->set([
                    'discordEnabled' => $command->isEnabledOnDiscord(),
                    'twitchEnabled' => $command->isEnabledOnTwitch(),
                    'cooldown' => $command->getCooldown(),
                    'output' => $command->getOutput(),
                    'handler' => $command->getHandler(),
                ]);
            } else {
                $cacheItem->set(null);
            }
            $cacheItem->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cacheItem);
        }

        $command = $cacheItem->get();
        if (null === $command || !$command[$enabledKey]) {
            return null;
        }

        // command cooldown
//        $cooldownKey = sprintf('cooldown_%s_%s', $enabledKey, $commandKey);
//        $cooldownItem = $this->cache->getItem($cooldownKey);
//        if (!$cooldownItem->isHit()) {
//            $cooldownItem->set('cooldown');
//            $cooldownItem->expiresAfter($command['cooldown']);
//            $this->cache->save($cooldownItem);
//        } else {
//            return null;
//        }

        return [
            'handler' => $command['handler'],
            'output' => $command['output'],
        ];
    }
}
