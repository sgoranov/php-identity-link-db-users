<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getUserById(string $id): ?User
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function getUserByUsernameAndPassword(string $username, string $password): ?User
    {
        $user = $this->findOneBy(['username' => $username]);
        if (is_null($user)) {
            return null;
        }

        if (password_verify($password, $user->getHashedPassword())) {
            return $user;
        }

        return null;
    }

    public function countUsersWithResetPasswordToken(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.resetToken IS NOT NULL')
            ->andWhere('u.resetTokenExpiresAt > :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Returns the distinct scopes granted to a user through their current groups.
     *
     * @return list<string>
     */
    public function getScopes(User $user, string $audience): array
    {
        return $this->getEntityManager()->getConnection()->fetchFirstColumn(
            <<<'SQL'
                SELECT DISTINCT gs.scope
                FROM group_scope gs
                INNER JOIN user_group ug ON ug.group_id = gs.group_id
                WHERE ug.user_id = :userId
                  AND gs.audience_hash = :audienceHash
                  AND gs.audience = :audience
                ORDER BY gs.scope
                SQL,
            [
                'userId' => $user->getId(),
                'audienceHash' => hash('sha256', $audience),
                'audience' => $audience,
            ]
        );
    }
}
