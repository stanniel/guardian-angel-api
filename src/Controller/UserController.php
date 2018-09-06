<?php

namespace App\Controller;

use App\Form\UserLoginType;
use App\Form\UserRegisterType;
use App\Entity\User;
use App\Form\UserSearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;

class UserController extends AbstractController
{
    private $normalizer;

    private $encoder;

    private $serializer;

    private $classMetadataFactory;

    /**
     * UserController constructor.
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct()
    {
        $this->classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $this->normalizer = new ObjectNormalizer($this->classMetadataFactory);
        $this->encoder = new JsonEncoder();
        $this->serializer = new Serializer([$this->normalizer], [$this->encoder]);
    }

    /**
     * @Route("/register", name="user_registration", methods={"POST"})
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em)
    {
        $user = new User();
        $form = $this->createForm(UserRegisterType::class, $user);

        if ($request->getMethod() == 'GET') {
            $data = $request->query->all();
        } else {
            $body = $request->getContent();
            $data = $this->serializer->decode($body, 'json');
        }

        $form->submit($data, false);

        if (!$form->isValid()) {
            throw new HttpException(400, "Request not valid");
        }

        $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);

        $em->persist($user);
        $em->flush();

        $json = $this->serializer->serialize($user, 'json', ['enable_max_depth' => true, 'groups' => ['public']]);

        return new Response($json, 201, ['Content-Type'  => 'application/json']);
    }

    /**
     * @Route("/login", name="user_login", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function loginAction(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em)
    {
        $body = $request->getContent();
        $data = $this->serializer->decode($body, 'json');

        $user = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if (!$user) {
            throw new HttpException(404, "User not found");
        }

        $form = $this->createForm(UserLoginType::class);
        $form->submit($data, false);

        if (!$form->isValid()) {
            throw new HttpException(400, "Request not valid");
        }

        if ($passwordEncoder->isPasswordValid($user, $data['password'])) {
            $json = $this->serializer->serialize($user, 'json', ['enable_max_depth' => true, 'groups' => ['public']]);
            return new Response($json, 200, ['Content-Type'  => 'application/json']);
        }

        throw new HttpException(401, "Authentication error");
    }

    /**
     * @Route("/search", name="user_search", methods={"GET"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function searchAction(Request $request, EntityManagerInterface $em)
    {
            $form = $this->createForm(UserSearchType::class);
            $data = $request->query->all();

            $form->submit($data, false);

            if (!$form->isValid()) {
                throw new HttpException(400, "Request not valid");
            }

            $results = $em->getRepository(User::class)->search($data);

            $json = $this->serializer->serialize($results, 'json',
                ['enable_max_depth' => true, 'groups' => ['public']]);
            $response = new Response();
            $response->setContent($json);
            $response->setStatusCode(200);
            //$response->headers->set('Content-Type', 'application/json');
            //$response->headers->set('Access-Control-Allow-Origin', '*');

            return $response;
    }

    /**
     * @Route("/user/{userId}/add-friend/{friendId}", name="user_add_friend", methods={"GET"})
     * @param $userId
     * @param $friendId
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function addFriendAction($userId, $friendId, EntityManagerInterface $em)
    {
        $user = $em->getRepository(User::class)->find($userId);
        $friend = $em->getRepository(User::class)->find($friendId);

        if (!$user) {
            throw new HttpException(404, "User not found");
        }

        if (!$friend) {
            throw new HttpException(404, "Friend not found");
        }

        $user->addFriend($friend);

        $em->persist($user);
        $em->persist($friend);
        $em->flush();

        $json = $this->serializer->serialize($user, 'json', ['enable_max_depth' => true, 'groups' => ['public']]);

        return new Response($json, 200, ['Content-Type'  => 'application/json']);
    }

    /**
     * @Route("/user/{userId}/remove-friend/{friendId}", name="user_remove_friend", methods={"GET"})
     * @param $userId
     * @param $friendId
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function removeFriendAction($userId, $friendId, EntityManagerInterface $em)
    {
        $user = $em->getRepository(User::class)->find($userId);
        $friend = $em->getRepository(User::class)->find($friendId);

        if (!$user) {
            throw new HttpException(404, "User not found");
        }

        if (!$friend) {
            throw new HttpException(404, "Friend not found");
        }

        $user->removeFriend($friend);

        $em->persist($user);
        $em->persist($friend);
        $em->flush();

        $json = $this->serializer->serialize($user, 'json', ['enable_max_depth' => true, 'groups' => ['public']]);

        return new Response($json, 200, ['Content-Type'  => 'application/json']);
    }
}

