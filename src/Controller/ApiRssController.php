<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

use FeedIo\FeedIo;
use GuzzleHttp\Client as GuzzleClient;

use App\Entity\RssFeed;
use App\Services\FindFederationLinkInHtml;
use App\Services\GuzzleForFeedIoFactory;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validation;

class ApiRssController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    private function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    #[Route("/api/rss", methods: ["GET", "HEAD"], name: "api_rss_list")]
    public function list()
    {
        $repository = $this->getDoctrine()->getRepository(RssFeed::class);
        $feeds = $repository->findBy([
            'user' => $this->getUser()->getUserIdentifier()
        ]);

        return $this->json($feeds);
    }

    #[Route("/api/rss/{id}", methods: ["GET"], name: "api_rss_get")]
    public function getRss(int $id, LoggerInterface $logger)
    {
        $feed = $this->getDoctrine()
            ->getRepository(RssFeed::class)
            ->find($id);

        if (!$feed) {
            throw $this->createNotFoundException(
                'No feed found for id ' . $id
            );
        }

        return $this->json($feed);
    }

    #[Route("/api/rss/{id}/entries", methods: ["GET"], name: "api_rss_get_entries")]
    public function getRssEntries(int $id, LoggerInterface $logger)
    {
        $feed = $this->getDoctrine()
            ->getRepository(RssFeed::class)
            ->find($id);

        if (!$feed) {
            throw $this->createNotFoundException(
                'No feed found for id ' . $id
            );
        }

        // create a simple FeedIo instance
        $client = new \FeedIo\Adapter\Http\Client(GuzzleForFeedIoFactory::create());
        $feedIo = new FeedIo($client, $logger);

        // read a feed
        try {
            $result = $feedIo->read($feed->getUrl());
        } catch (\FeedIo\Reader\ReadErrorException $e) {
            $error = ($_SERVER['APP_ENV'] !== 'prod') ? '{"error": "' . $e->getMessage() . '"}' : '';
            return $this->json(
                null,
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json($result->getFeed());
    }

    #[Route("/api/rss", methods: ["POST"], name: "api_rss_post")]
    public function create(Request $request, LoggerInterface $logger)
    {

        $content = $request->getContent();
        $json = json_decode($content);

        if ($json === null || !isset($json->url)) {
            return $this->json(['errors' => ['Missing url field']], Response::HTTP_BAD_REQUEST);
        }

        $feed = new RssFeed();
        $feed->setUrl($json->url);
        $feed->setUser($this->getUser());

        $validator = Validation::createValidator();
        $errors = $validator->validate($feed);

        if (count($errors)) {
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($feed);
        $em->flush();

        return $this->json(
            $feed,
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl('api_rss_get', ['id' => $feed->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            ]
        );
    }

    #[Route("/api/rss/prefetch", methods: ["POST"], name: "api_rss_post_prefetch")]
    public function prefetch(Request $request, LoggerInterface $logger, FindFederationLinkInHtml $finder)
    {
        $content = $request->getContent();
        $json = json_decode($content);

        if ($json === null || !isset($json->url)) {
            return $this->json(['errors' => ['Missing url field']], Response::HTTP_BAD_REQUEST);
        }

        $url = $json->url;

        $ok = false;
        $found = [];

        /*
        * Is this a rss feed ?
        */
        try {
            $client = new \FeedIo\Adapter\Http\Client(GuzzleForFeedIoFactory::create());
            $feedIo = new FeedIo($client, $logger);
            $result = $feedIo->read($json->url);
            $feedTitle = $result->getFeed()->getTitle();
            //echo "<br>\nIs a rss feed<br>\n";
            $found[] = $url;
            $ok = true;
        } catch (\FeedIo\Reader\ReadErrorException $e) {
            //echo "<br>\nNot a rss feed<br>\n";
            $logger->debug('Not a rss feed', [$url, $e]);
        } catch (\Exception $e) {
            //echo "<br>\nNot a rss feed at all<br>\n";
            $logger->debug('Not a rss feed at all', [$e]);
        }

        if (!$ok) {
            $client = new \GuzzleHttp\Client();
            $res = $client->request('GET', $url);
            if ($res->getStatusCode() === 200) {
                $body = $res->getBody();

                $found = $finder->find($url, $body);
                if (count($found) > 0) {
                    $ok = true;
                }
            }
        }

        return $this->json(array_values($found));
    }

    #[Route("/api/rss/{id}", methods: ["DELETE"], name: "api_rss_delete")]
    public function delete(int $id)
    {
        $feed = $this->getDoctrine()
            ->getRepository(RssFeed::class)
            ->find($id);

        if (!$feed) {
            throw $this->createNotFoundException(
                'No feed found for id ' . $id
            );
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($feed);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
