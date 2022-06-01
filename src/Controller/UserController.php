<?php

namespace App\Controller;

use App\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use JsonException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use App\Service\ExceptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Form\UserFormType;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use App\Service\PaginationFactory;
use Error;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends AbstractApiController
{
    public function __construct(UserPasswordHasherInterface $userPasswordHasher, ValidatorInterface $validator, SerializerInterface $serializer)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }
    
    /**
     * @Route("/users", name="users_list")
     * @OA\Get(summary="Get list of your organization's users")
     * @OA\Response(
     *     response=JsonResponse::HTTP_OK,
     *     description="Returns the list of your users"
     * )
     * @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Number of the page you want to see",
     *          example=1,
     *      )
     * @OA\Tag(name="Users")
     * @Cache(maxage="1 hour", public=true)
     */
    public function list(UserRepository $userRepository, SerializerInterface $serializer, PaginationFactory $paginationFactory, Request $request): JsonResponse
    {   
        // GET only users related to the same customer as the current authenticated user
        /* @var Customer */
        $customer = $this->getUser()->getCustomer();
        $query=  $userRepository->findByCustomerQueryBuilder($customer);
        $paginatedCollection = $paginationFactory->createCollection($query, $request, 'users_list', [],3);
        return new JsonResponse(
            $serializer->serialize($paginatedCollection,"json", SerializationContext::create()->setGroups(['users:list'])),
            JsonResponse::HTTP_OK, [], true
        );
    }

    /**
     * @Route("/users/{userId}", name="user_details", methods={"GET"})
     * @OA\Get(summary="Get details of a user")
     * @OA\Parameter(
     *          name="userId",
     *          in="path",
     *          description="Unique identifier of the user",
     *          required=true,
     *          example=1,
     *      )
     * @OA\Response(
     *     response=JsonResponse::HTTP_OK,
     *     description="Returns a user",
     *     @Model(type=User::class)
     * )
     * @OA\Response(
     *     response=JsonResponse::HTTP_UNAUTHORIZED,
     *     description="Unauthorized request"
     * )
     * @OA\Response(
     *     response=JsonResponse::HTTP_NOT_FOUND,
     *     description="User not found"
     * )
     * @OA\Tag(name="Users")
     * @Cache(maxage="1 hour", public=true)
     * @return JsonResponse
     * @param User $user
     */
    public function details(UserRepository $userRepository, Request $request)
    {   
        $userId = $request->get('userId');
        $user = $userRepository->findOneBy([
            'id' => $userId
        ]);
        $customer = $this->getUser()->getCustomer();

        if (!$user || !$customer) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
            //throw new NotFoundHttpException("The user was not found");
        }

        if ($user->getCustomer()->getId() !== $customer->getId()) {
            return new JsonResponse("You don't have the rights to see this user's details", Response::HTTP_FORBIDDEN);
        }

        
        
        return new JsonResponse(
            $this->serializer->serialize($user,"json", SerializationContext::create()->setGroups(['user:details'])),
            JsonResponse::HTTP_OK, [], true
        );

    }

    /**
     * @Route("/api/users", name="user_create", methods={"POST"})
     * @OA\Post(summary="Add a new user for your organization")
     * @OA\RequestBody(
     *     description="The new user to create",
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/Json",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="userName",
     *                 description="UserName for user identification",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 description="User's choosen password, at least 6 characters",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="firstName",
     *                 description="User's first name",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="lastName",
     *                 description="User's last name",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 description="User's email address",
     *                 type="string"
     *             )
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=JsonResponse::HTTP_CREATED,
     *     description="User created"
     * )
     * @OA\Response(
     *     response=JsonResponse::HTTP_BAD_REQUEST,
     *     description="Bad Json syntax or incorrect data"
     * )
     * @OA\Response(
     *     response=JsonResponse::HTTP_UNAUTHORIZED,
     *     description="Unauthorized request"
     * )
     * @OA\Tag(name="Users")
     */
    public function create(EntityManagerInterface $entityManager, Request $request)
    {   
        
        $form =$this->buildForm(UserFormType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()){

        
        /** @var User $user */
        
        $user = $form->getData();
        /*$user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        /*if ($errorMessages = $this->getValidationErrors($user)) {
            return $this->throwValidationErrors($user);
        }*/
        $user->setCustomer($this->getUser()->getCustomer());
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));
        
        $user->setRoles(["ROLE_USER"]);
        //dd($customer);
       
        //$customer->addUser($user);
        /*$errors = $this->validateUser($user);
            if ($errors) {
                return $errors;
            }*/
        if (!$this->isGranted("ROLE_ADMIN", $user)) {
            return new JsonResponse("Vous n'êtes pas autorisé à effectuer cette requête",Response::HTTP_UNAUTHORIZED);
        }
        $entityManager->persist($user);
        $entityManager->flush();
 
        //return $this->View($user, 201);}
        return new JsonResponse(
            $this->serializer->serialize("User created!","json"),
            201, [], true
        );
    }
    if (!$form){
        return $this->respond("Vous devez fournir des informations",Response::HTTP_BAD_REQUEST);
    }
    return $this->respond($form, Response::HTTP_BAD_REQUEST);
        /*catch(Error $e) {
            return new JsonResponse(
                $serializer->serialize($e->getMessage(),"json"),
                403, [], true
            );
        }*/

    }

    /**
     * @Route("/api/users/{userId}", name="user_delete", methods={"DELETE"})
     * @OA\Delete(summary="Delete a user")
     * @OA\Parameter(
     *          name="userId",
     *          in="path",
     *          description="Unique identifier of the user",
     *          required=true,
     *          example=1,
     *      )
     * @OA\Response(
     *     response=JsonResponse::HTTP_NO_CONTENT,
     *     description="Delete a user"
     * )
     * @OA\Response(
     *     response=JsonResponse::HTTP_UNAUTHORIZED,
     *     description="Unauthorized request"
     * )
     * @OA\Response(
     *     response=JsonResponse::HTTP_FORBIDDEN,
     *     description="You can't delete yourself"
     * )
     * @OA\Response(
     *     response=JsonResponse::HTTP_NOT_FOUND,
     *     description="User not found"
     * )
     * @OA\Tag(name="Users")
     */
    public function delete(EntityManagerInterface $entityManager, UserRepository $userRepository, Request $request) :Response
    {
        $userId = $request->get('userId');
        $customerId = $this->getUser()->getCustomer()->getId();
        $user = $userRepository->findOneBy([
            'customer' => $customerId,
            'id' => $userId
        ]);
        $currentUserId = $this->getUser()->getId();
        //dd($userId);
        if($currentUserId == $userId){
            return $this->respond("You can't delete yourself", Response::HTTP_FORBIDDEN);
        }
        if(!$user){
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        // if user can't be deleted by the current user
        if (!$this->isGranted("ROLE_ADMIN", $user)) {
            return new JsonResponse("You don't have the rights to delete a user", Response::HTTP_UNAUTHORIZED);
        }
        $entityManager->remove($user);
        $entityManager->flush();
        return new JsonResponse(
            null,
            JsonResponse::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/api/users/{userId}", name="user_update", methods={"PATCH"})
     * @OA\Patch(summary="Update a user")
     * @OA\Parameter(
     *          name="userId",
     *          in="path",
     *          description="Unique identifier of the user",
     *          required=true,
     *          example=1,
     *      )
     * @OA\Response(
     *     response=JsonResponse::HTTP_ACCEPTED,
     *     description="User updated"
     * )
     * @OA\Response(
     *     response=JsonResponse::HTTP_BAD_REQUEST,
     *     description="Bad Json syntax or incorrect data"
     * )
     * @OA\Response(
     *     response=JsonResponse::HTTP_UNAUTHORIZED,
     *     description="Unauthorized request"
     * )
     * @OA\Response(
     *     response=JsonResponse::HTTP_NOT_FOUND,
     *     description="User not found"
     * )
     * @OA\RequestBody(
     *     description="The user data you want to update.If you don't want to change a field don't mention it",
     *     @OA\MediaType(
     *         mediaType="application/Json",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="userName",
     *                 description="User's userName",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 description="User's choosen password, 6 caractères minimum",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="firstName",
     *                 description="User's first name",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="lastName",
     *                 description="User's last name",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 description="User's email address",
     *                 type="string"
     *             )
     *         )
     *     )
     * )
     * @OA\Tag(name="Users")
     */
    public function update(SerializerInterface $serializer, UserRepository $userRepository, EntityManagerInterface $entityManager, CustomerRepository $customerRepository, Request $request) :Response
    {
        $userId = $request->get('userId');
        $customerId = $this->getUser()->getCustomer()->getId();
        $customer = $customerRepository->findOneBy([
            'id' => $customerId
        ]);
        if(!$customer){
            return $this->respond("This customer doesn't exit",Response::HTTP_NOT_FOUND);
        }
        
        $user = $userRepository->findOneBy([
            'customer' => $customerId,
            'id' => $userId
        ]);
        if(!$user){
            return $this->respond("This user doesn't exit",Response::HTTP_NOT_FOUND);
        }
        $form= $this->buildForm(UserFormType::class, $user, [
            'method' => $request->getMethod()
        ]);
        if (!$this->isGranted("ROLE_ADMIN", $user)) {
            return $this->respond("Vous n'êtes pas autorisé à effectuer cette requête",Response::HTTP_UNAUTHORIZED);
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            

            $user = $form->getData();
            $entityManager->persist($user);
            $entityManager->flush();
            return new JsonResponse(
                $serializer->serialize("User updated","json"),
                202, [], true
            );
        }

        return $this->respond($form, Response::HTTP_BAD_REQUEST);
        //return $this->respond($user);
    }

    /**
     * checkUser
     *
     * @param  mixed $user
     * @return void
     */
    protected function checkUser($user)
    {
        // if user is not found
        if (!$user || !($user instanceof User)) {
            throw new JsonException("Incorrect identifier or no user found with this identifier", JsonResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * validateUser
     *
     * @param  mixed $user
     * @return mixed void|JsonResponse
     */
    protected function validateUser($user)
    {
        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            return new JsonResponse(
                $this->serializer->serialize($errors, 'json'),
                JsonResponse::HTTP_BAD_REQUEST,
                [],
                true
            );
        }
    }
}
