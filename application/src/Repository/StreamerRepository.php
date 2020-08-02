<?php

namespace Krushed\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Krushed\Entity\Streamer;

/**
 * @method Streamer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Streamer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Streamer[]    findAll()
 * @method Streamer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StreamerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Streamer::class);
    }
}
