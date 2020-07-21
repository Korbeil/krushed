<?php

namespace Krushed\Controller\Authentication;

use Doctrine\ORM\EntityManagerInterface;
use Krushed\Entity\Streamer;
use Krushed\Repository\StreamerRepository;
use Krushed\Service\Authentication\Twitch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class TwitchController extends AbstractController
{
    /**
     * @Route("/twitch/redirect", name="twitch_authorize_callback")
     */
    public function callback(Request $request, Twitch $twitch, StreamerRepository $repository, EntityManagerInterface $entityManager): Response
    {
        if ($request->query->get('state') !== $request->getSession()->get('oauth_state')) {
            throw new BadRequestHttpException('Wrong state token.');
        }

        $name = $request->getSession()->get('oauth_user');
        $data = $twitch->getTokens($name, $request->query->get('code'));

        $streamer = $repository->findOneBy(['name' => $name]);
        if (null === $streamer) {
            $streamer = new Streamer();
            $streamer->setName($name);
            $streamer->setChannelId($data['channel_id']);
        }

        $streamer->setToken($data['access_token']);
        $streamer->setRefreshToken($data['refresh_token']);
        $entityManager->persist($streamer);
        $entityManager->flush();

        return new Response('callback');
    }

    /**
     * @Route("/twitch/{user}", name="twitch_authorize")
     */
    public function authorize(Request $request, Twitch $twitch): Response
    {
        $state = md5(random_bytes(10));
        $request->getSession()->set('oauth_state', $state);
        $request->getSession()->set('oauth_user', mb_strtolower($request->attributes->get('user')));

        return $this->redirect($twitch->getAuthorizeUrl($state));
    }
}
