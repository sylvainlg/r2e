<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Psr\Log\LoggerInterface;

use App\Entity\RssFeed;

use FeedIo\FeedIo;
use App\Services\FindFederationLinkInHtml;
use App\Services\GuzzleForFeedIoFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;

/**
 * TODO
 */
class MainController extends AbstractController
{

    private $addForm;
    private $choiceAddForm;

    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    private function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    private function getAddForm()
    {

        if (!$this->addForm) {

            $this->addForm = $this->createFormBuilder(new RssFeed())
                ->setAction($this->generateUrl('add'))
                ->add('url', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'https://...']])
                ->add('save', SubmitType::class, ['label' => 'Ajouter', 'attr' => ['class' => 'btn btn-primary']])
                ->getForm();
        }

        return $this->addForm;
    }

    private function getChoiceAddForm(array $choices = [])
    {

        if (!$this->choiceAddForm) {

            $this->choiceAddForm = $this->createFormBuilder(new RssFeed())
                ->setAction($this->generateUrl('do_add'))
                ->add('url', ChoiceType::class, [
                    'label' => 'Choix : ',
                    'choices'     => $choices,
                    'expanded'    => true,
                    'multiple'    => false,
                    'attr' => ['class' => 'radio radio-info']
                ])
                ->add('save', SubmitType::class, ['label' => 'Valider l\'ajout', 'attr' => ['class' => 'btn btn-primary']])
                ->getForm();
        }

        return $this->choiceAddForm;
    }

    #[Route("/", name: "index")]
    public function index(Request $request)
    {

        $feed = new RssFeed();
        $form = $this->getAddForm();

        $form->handleRequest($request);

        $repository = $this->getDoctrine()->getRepository(RssFeed::class);
        $feeds = $repository->findBy([
            'user' => $this->getUser()
        ]);

        return $this->render('main/index.html.twig', [
            'name' => $this->getUser()->getUserIdentifier(),
            'feeds' => $feeds,
            'form' => $form->createView()
        ]);
    }

    #[Route("/add", name: "add")]
    public function add(Request $request, LoggerInterface $logger, FindFederationLinkInHtml $finder)
    {

        /*
		 * Détection rss :
		 * 	\<[^>]*type="application\/rss\+xml"[^>]*>
		 * Détection atom :
		 * 	\<[^>]*type="application\/atom\+xml"[^>]*>
		 * Recherche d'un lien avec /feed/ dedans (https?:\/\/[siteurl]\/[^"]*feed[^"]*)"
		 * Recherche d'un lien avec rss dedans : (https?:\/\/[siteurl]\/[^"]*rss[^"]*)"
		 * 
		 * 
		 * Recherche sur la page d'accueil du site pour trouver avec les éléments précédents.
		 * 
		 */

        $form = $this->getAddForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $feed = $form->getData();
            /*$feed->setUser($this->getUser()->getUserName());
	
			// ... perform some action, such as saving the task to the database
			// for example, if Task is a Doctrine entity, save it!
			$em = $this->getDoctrine()->getManager();
			$em->persist($feed);
			$em->flush();*/

            // var_dump($feed);

            $url = $feed->getUrl();

            $ok = false;
            $found = [];

            /*
			* Is this a rss feed ?
			*/
            try {
                // $feedIo = \FeedIo\Factory::create()->getFeedIo();
                $client = new \FeedIo\Adapter\Http\Client(GuzzleForFeedIoFactory::create());
                $feedIo = new FeedIo($client);

                $result = $feedIo->read($feed->getUrl());
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

            $choices = array_combine($found, $found);
            $formChoice = $this->getChoiceAddForm($choices);

            return $this->render('main/add.html.twig', [
                'feed' => $feed,
                'ok' => $ok,
                'found' => $found,
                'form' => $formChoice->createView()
            ]);
        }

        return $this->redirectToRoute('index');
    }

    #[Route("/do_add", name: "do_add")]
    public function do_add(Request $request)
    {

        $form = $this->getAddForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $feed = $form->getData();
            $feed->setUser($this->getUser());

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $em = $this->getDoctrine()->getManager();
            $em->persist($feed);
            $em->flush();
        }

        return $this->redirectToRoute('index');
    }

    #[Route("/feed/{id}", name: "feed")]
    public function feed($id, LoggerInterface $logger)
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
            $args = [
                'feed' => $feed,
                'items' => []
            ];
            $args['error'] = ($_SERVER['APP_ENV'] !== 'prod') ? $e->getMessage() : '';
            return $this->render('main/feed.html.twig', $args);
        }


        // or read a feed since a certain date
        // $result = $feedIo->readSince($url, new \DateTime('-7 days'));

        // get title
        $feedTitle = $result->getFeed()->getTitle();

        // iterate through items
        /*foreach( $result->getFeed() as $item ) {
			echo $item->getTitle();
		}*/

        return $this->render('main/feed.html.twig', [
            'feed' => $feed,
            'items' => $result->getFeed(),
            'error' => 'no error',
        ]);
    }

    #[Route("feed/{id}/delete")]
    public function delete($id)
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

        return $this->redirectToRoute('index');
    }

    #[Route("feed/{id}/email")]
    public function email($id, KernelInterface $kernel, LoggerInterface $log)
    {

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'app:fetchfeed',
            'feed' => $id,
        ]);

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);

        // return the output, don't use if you used NullOutput()
        $content = $output->fetch();
        $log->debug($content);

        return $this->redirectToRoute('feed', ['id' => $id]);
    }
}
