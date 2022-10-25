<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Entity\RssFeed;
use App\Entity\User;
use App\Command\Configuration\AppFetchfeedsConfiguration;
use App\Command\Processor\FeedProcessor;
use App\Command\Processor\UserFeedsProcessor;

use Symfony\Component\Mailer\MailerInterface;

class AppFetchfeedsCommand extends Command
{
    protected static $defaultName = 'app:fetchfeeds';

    private $doctrine;
    private $twig;

    public function __construct(
        \Doctrine\ORM\EntityManagerInterface $pDoctrine,
        \Twig\Environment $pTwig,
        private MailerInterface $mailer
    ) {
        parent::__construct();
        $this->doctrine = $pDoctrine;
        $this->twig = $pTwig;
    }

    protected function configure()
    {
        $this
            ->setDescription('Fetch feeds')
            ->addArgument('feed', InputArgument::OPTIONAL, 'Feed ID')
            ->addArgument('user', InputArgument::OPTIONAL, 'User email')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Does not send any mail nor save the tick in database (no update on last_update)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $feedId = $input->getArgument('feed');
        $userEmail = $input->getArgument('user');

        $dryRun = (bool) $input->getOption('dry-run');
        if ($dryRun) {
            $io->caution('You have activated the dry run mode');
        }

        $configuration = new AppFetchfeedsConfiguration($io, $this->doctrine, $this->twig, $dryRun, $feedId, $userEmail);

        $io->title('Starting watching some fresh news in feeds !');

        if (!is_null($configuration->getFeedId())) {
            $feedRepo = $this->doctrine->getRepository(RssFeed::class);
            $feed = $feedRepo->find($feedId);
            (new FeedProcessor($configuration, $feed, $this->mailer))->run();
        } else {
            $userRepo = $this->doctrine->getRepository(User::class);
            if (is_null($userEmail)) {
                $users = $userRepo->findAll();
                foreach ($users as $user) {
                    (new UserFeedsProcessor($configuration, $user, $this->mailer))->run();
                }
            } else {
                $user = $userRepo->findOneByEmail($userEmail);
                if (!is_null($user)) {
                    (new UserFeedsProcessor($configuration, $user, $this->mailer))->run();
                }
            }
        }

        $this->doctrine->flush();
        $io->success('If there are some news, there are in your inbox :-) See you on next tick !');

        return 0;
    }
}
