<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a user and optionally attach groups by name.'
)]
final class CreateUserCommand extends AbstractCommand
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
        private readonly EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ) {
        parent::__construct($validator);
    }

    protected function configure(): void
    {
        $this
            ->addOption('username', null, InputOption::VALUE_REQUIRED, 'Username for the new user.')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email address for the new user.')
            ->addOption('first-name', null, InputOption::VALUE_REQUIRED, 'First name for the new user.')
            ->addOption('last-name', null, InputOption::VALUE_REQUIRED, 'Last name for the new user.')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Password for the new user.')
            ->addOption(
                'grant-type',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'OAuth2 grant type to allow. This option may be repeated.',
            )
            ->addOption(
                'group',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Group name to attach. This option may be repeated.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $requiredOptions = ['username', 'email', 'first-name', 'last-name', 'password'];

        foreach ($requiredOptions as $option) {
            if (!$this->isValidStringOption($input, $option)) {
                $io->error(sprintf('The --%s option is required.', $option));

                return Command::INVALID;
            }
        }

        $groups = $this->resolveRequestedGroups($input, $io);
        if ($groups === null) {
            return Command::INVALID;
        }

        $user = new User();
        $user->setUsername($input->getOption('username'));
        $user->setEmail($input->getOption('email'));
        $user->setFirstName($input->getOption('first-name'));
        $user->setLastName($input->getOption('last-name'));
        $user->setPassword($input->getOption('password'));
        $user->setGroups(new ArrayCollection($groups));
        $user->setGrantTypes(array_values(array_unique($input->getOption('grant-type'))));
        $user->setTwoFaEnabled(false);

        try {
            $this->assertEntityIsValid($io, $user);
        } catch (ValidationFailedException $exception) {
            return Command::INVALID;
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $message = sprintf('User "%s" was created.', $user->getUsername());
        $io->success($message);

        return Command::SUCCESS;
    }

    private function resolveRequestedGroups(InputInterface $input, SymfonyStyle $io): ?array
    {
        /** @var list<string> $requestedGroupNames */
        $requestedGroupNames = array_values(array_unique($input->getOption('group')));

        if ($requestedGroupNames === []) {
            return [];
        }

        $groups = $this->groupRepository->findBy(['name' => $requestedGroupNames]);
        $foundGroupNames = array_map(static fn ($group): string => $group->getName(), $groups);
        $missingGroupNames = array_values(array_diff($requestedGroupNames, $foundGroupNames));

        if ($missingGroupNames !== []) {
            $io->error(sprintf('Unknown group(s): %s.', implode(', ', $missingGroupNames)));

            return null;
        }

        return $groups;
    }
}
