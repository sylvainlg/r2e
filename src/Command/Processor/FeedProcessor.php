<?php

namespace App\Command\Processor;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FeedIo\FeedIo;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Command\Configuration\AppFetchfeedsConfiguration;
use App\Services\GuzzleForFeedIoFactory;
use App\Entity\RssFeed;
use App\Entity\LogEvent;
use App\Entity\FEED_STATUS;
use App\Command\Exception\FeedProcessException;
use FeedIo\Adapter\Http\Client as HttpClient;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class FeedProcessor
{
    private $conf;
    private $feed;

    private $io;

    public function __construct(AppFetchfeedsConfiguration $configuration, RssFeed $feed, private MailerInterface $mailer)
    {
        $this->conf = $configuration;
        $this->feed = $feed;

        $this->io = $this->conf->getIo();
    }

    public function run()
    {

        if ($this->feed->getEnabled() === false) {
            return;
        }

        try {
            $this->processFeed();
        } catch (\FeedIo\Reader\ReadErrorException $e) {
            $this->io->warning('Error with feed ' . $this->feed->getUrl() . ' : ' . $e->getMessage());
            $this->io->warning($e->getPrevious()->getTraceAsString());

            $this->handleError($e, FEED_STATUS::KO);

            throw new FeedProcessException($e->getMessage(), $e->getPrevious());
        } catch (\RuntimeException $e) {
            $this->io->caution($e->getMessage());
            $this->io->caution($e->getTraceAsString());

            $this->handleError($e, FEED_STATUS::KO);

            throw new FeedProcessException($e->getMessage(), $e);
        }
    }

    private function processFeed()
    {

        if (!$this->feed) {
            throw new NotFoundHttpException(
                'No feed not set'
            );
        }

        $this->io->section('Fetch feed #' . $this->feed->getId() . ' - ' . $this->feed->getUrl());

        $client = new HttpClient(GuzzleForFeedIoFactory::create());
        $logger = new Logger('default', [new StreamHandler('php://stdout')]);

        $this->feedIo = new FeedIo($client, $logger);


        // read a feed
        $lastUpdate = $this->feed->getLastUpdate();
        if ($lastUpdate !== null) {
            // $result = $this->feedIo->readSince($this->feed->getUrl(), $lastUpdate);
            $result = $this->feedIo->read($this->feed->getUrl(), null, $lastUpdate);
            $this->io->text('Read feed since ' . $lastUpdate->format('d/m/Y H:i:s'));
        } else {
            $result = $this->feedIo->read($this->feed->getUrl());
            $this->io->text('Read complete feed');
        }

        $this->feedItems = $result->getFeed();
        $this->io->text('Found : ' . $this->feedItems->count() . ' item' . (($this->feedItems->count() > 1) ? 's' : ''));
        $mailsent = $this->feedItems->count() === 0;

        foreach ($this->feedItems as $item) {
            $authorName = ($item->getAuthor() ? $item->getAuthor()->getName() : '');
            $author = $this->feedItems->getTitle() . (($authorName !== null) ? ': ' . $authorName : '');

            $pubId = preg_replace('#https?://#', '', $this->feedItems->getPublicId());
            $title = $author . ' - ' . $item->getTitle();

            $item->setContent(mb_convert_encoding($item->getContent(), 'utf-8', 'auto'));

            $to = $this->feed->getUser()->getEmail();
            $body = $this->conf->getTwig()->render(
                'emails/feed_item.html.twig',
                [
                    'feed' => $this->feedItems,
                    'item' => $item
                ]
            );

            // Attention, ne pas rajouter de champ From dans les entêtes
            if (!$this->conf->isDryRun()) {
                $email = (new Email())
                    ->to($to)
                    ->subject($title)
                    ->html($body);
                try {
                    $this->mailer->send($email);
                } catch (TransportExceptionInterface $e) {
                    $mailsent = false;
                }
                $this->io->text('Sending mail : ' . $title);
            } else {
                $this->io->comment('Sending mail : ' . $title);
            }
        }

        if (!$this->conf->isDryRun() && $mailsent) {
            $this->feed->setLastUpdate(new \Datetime());
            $this->conf->getDoctrine()->persist($this->feed);
        }
    }

    private function handleError($err)
    {
        $this->feed->setStatus(FEED_STATUS::KO);

        $log = new LogEvent();
        $log->setFeed($this->feed);
        $log->setDatetime(new \Datetime());
        $log->setMessage($err->getMessage());
        $log->setTrace($err->getPrevious()->getTraceAsString());
        $this->conf->getDoctrine()->persist($log);
    }
}
