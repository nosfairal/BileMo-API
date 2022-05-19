<?php

namespace App\Controller;

use App\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use JsonException;
use App\Service\ExceptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Form\UserFormType;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Error;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
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
     */
    public function list(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {   
        // GET only users related to the same customer as the current authenticated user
        /* @var Customer */
        $customer = $this->getUser()->getCustomer();
        $users =  $userRepository->findByCustomer($customer->getId());
        //\dd($users);
        return new JsonResponse(
            $serializer->serialize($users,"json", ["groups" => "users:list"]),
            JsonResponse::HTTP_OK, [], true
        );
    }

    /**
     * @Route("/users/{userId}", name="users_details", methods={"GET"})
     * @OA\Get(summary="Get details of a user")
     * @OA\Response(
     *     response=JsonResponse::HTTP_OK,
     *     description="Returns a user"
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

        if ($user->getCustomer()->getId() !== $customer->getId()) {
            return $this->respond("You don't have the rights to see this user's details", Response::HTTP_FORBIDDEN);
        }

        if (!$user) {
            return $this->respond("This user doesn't exist", Response::HTTP_NOT_FOUND);
            //throw new NotFoundHttpException("The user was not found");
        }
        
        return new JsonResponse(
            $this->serializer->serialize($user,"json", ["groups" => "user:details"]),
            JsonResponse::HTTP_OK, [], true
        );

    }

    /**
     * @Route("/api/user/create", name="user_create", methods={"POST"})
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
     *                 description="User's choosen password",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="first_name",
     *                 description="User's first name",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="last_name",
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
     *     description="Create a user and returns it"
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
            return $this->respond("Vous n'êtes pas autorisé à effectuer cette requête",Response::HTTP_UNAUTHORIZED);
        }
        $entityManager->persist($user);
        $entityManager->flush();
 
        //return $this->View($user, 201);}
        return new JsonResponse(
            $this->serializer->serialize("User created!","json"),
            201, [], true
        );
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
     * @Route("/api/user/{userId}", name="user_delete", methods={"DELETE"})
     * @OA\Delete(summary="Delete a user")
     * @OA\Response(
     *     response=JsonResponse::HTTP_NO_CONTENT,
     *     description="Delete a user"
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
     */
    public function delete(EntityManagerInterface $entityManager, UserRepository $userRepository, Request $request) :Response
    {
        $userId = $request->get('userId');
        $customerId = $this->getUser()->getCustomer()->getId();
        $user = $userRepository->findOneBy([
            'customer' => $customerId,
            'id' => $userId
        ]);
        if(!$user){
            return $this->respond("This user doesn't exit",Response::HTTP_NOT_FOUND);
        }
        // if user can't be deleted by the current user
        if (!$this->isGranted("ROLE_ADMIN", $user)) {
            return $this->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'message' => "Vous n'êtes pas autorisé à effectuer cette requête"
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $entityManager->remove($user);
        $entityManager->flush();
        return new JsonResponse(
            null,
            JsonResponse::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/api/user/{userId}", name="user_update", methods={"PATCH"})
     * @OA\Patch(summary="Update a user")
     * @OA\Response(
     *     response=JsonResponse::HTTP_OK,
     *     description="Update a user and returns it"
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
     *                 description="User's choosen password",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="first_name",
     *                 description="User's first name",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="last_name",
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
