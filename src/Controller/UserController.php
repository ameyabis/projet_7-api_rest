<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $userPasswordHasher,
        private TagAwareCacheInterface $cachePool,
        private SerializerInterface $serializer
    ) {
    }

    /**
     * Cette méthode permet de récupérer tous les utilisateurs présent dans votre compagnie.
     */
    #[Route('/api/user', name: 'all_users', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des utilisateurs',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['getUsers']))
        )
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'Choisissez la page souhaité',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Le nombre d\'utilisateur affiché',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'User')]
    public function getUsers(
        #[CurrentUser] ?User $currentUser,
        Request $request
    ): JsonResponse {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = 'getAllUser-'.$currentUser->getCustomer()->getName().'-'.$page.'-'.$limit;
        $users = $this->cachePool->get(
            $idCache,
            function (ItemInterface $item) use ($page, $limit, $currentUser) {
                $item->tag('usersCache');

                return $this->em->getRepository(User::class)->findAllUserPagination($page, $limit, $currentUser);
            }
        );

        $context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonUser = $this->serializer->serialize($users, 'json', $context);

        return new JsonResponse($jsonUser, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Cette méthode permet de récupérer un utilisateur présent dans votre compagnie.
     */
    #[Route('/api/user/{id}', name: 'one_user', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste d\'un utilisateur',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['getUsers']))
        )
    )]
    #[OA\Tag(name: 'User')]
    public function getOneUser(
        int $id,
        #[CurrentUser] ?User $currentUser,
        User $user
    ): JsonResponse {
        $userSearch = $this->em->getRepository(User::class)->findOneBy([
            'id' => $id,
            'customer' => $currentUser->getCustomer(),
        ]);

        $context = SerializationContext::create()->setGroups(['getUsers']);
        $userSearch = $this->serializer->serialize($userSearch, 'json', $context);

        return new JsonResponse($userSearch, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de supprimer un utilisateur présent dans votre compagnie.
     */
    #[Route('/api/user/{id}', name: 'delete_user', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    #[OA\Response(
        response: 204,
        description: 'Retourne la liste des utilisateurs',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['getUsers']))
        )
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'Choisissez la page souhaité',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Le nombre d\'utilisateur affiché',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'User')]
    public function deleteUser(
        User $user
    ): JsonResponse {
        $this->em->remove($user);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Cette méthode permet de créer un utilisateur pour votre compagnie.
     */
    #[Route('/api/user', name: 'create_user', methods: ['POST'])]
    #[OA\Response(
        response: 201,
        description: 'Création d\'un nouveau utilisateur',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['getUsers']))
        )
    )]
    #[OA\Parameter(
        name: 'username',
        in: 'query',
        description: 'Nom d\'utilisateur',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'password',
        in: 'query',
        description: 'Mot de passe',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'firstname',
        in: 'query',
        description: 'Prénom',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'lastname',
        in: 'query',
        description: 'Nom de famille',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'email',
        in: 'query',
        description: 'Adresse email',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'User')]
    public function createUser(
        UrlGeneratorInterface $urlGenerator,
        #[CurrentUser] ?User $currentUser,
        Request $request,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));
        $user->setCustomer($currentUser->getCustomer());

        $errors = $validator->validate($user);

        if ($errors->count() > 0) {
            return new JsonResponse($this->serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $this->em->persist($user);
        $this->em->flush();

        $context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonUser = $this->serializer->serialize($user, 'json', $context);

        $location = $urlGenerator->generate(
            'one_users',
            ['id' => $user->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse(
            $jsonUser,
            Response::HTTP_CREATED,
            ['Location' => $location],
            true
        );
    }
}
