<?php
declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reset-user-password',
    description: 'Reset user password.'
)]
class ResetUserPasswordCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'username',
            null,
            InputOption::VALUE_REQUIRED,
            'Username whose password should be reset.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string|null $username */
        $username = $input->getOption('username');
        if ($username === null || $username === '') {
            $io->error('The --username option is required.');

            return Command::INVALID;
        }

        $user = $this->userRepository->findOneBy(['username' => $username]);
        if (!$user) {
            $io->error(sprintf('User "%s" was not found.', $username));

            return Command::INVALID;
        }

        $password = rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
        $user->setPassword($password);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf(
            'Password for user "%s" has been reset. New password: %s',
            $username,
            $password,
        ));
        return Command::SUCCESS;
    }
}