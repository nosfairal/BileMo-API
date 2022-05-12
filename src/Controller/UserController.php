<?php

namespace App\Controller;

use App\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\UserFormType;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Error;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends AbstractApiController
{
    public function __construct(UserPasswordHasherInterface $userPasswordHasher, SerializerInterface $serializer)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->serializer = $serializer;
    }
    
    /**
     * @Route("/customers/{customerId}/users", name="users_list")
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
     * @Route("/customers/{customerId}/users/{userId}/details", name="users_details", methods={"GET"})
     * @return JsonResponse
     * @param User $user
     */
    public function details(UserRepository $userRepository, Request $request)
    {   
        $customerId = $request->get('customerId');
        $userId = $request->get('userId');
        $user = $userRepository->findOneBy([
            'id' => $userId,
            'customer' => $customerId

        ]);
        if (!$user) {
            return $this->respond("This user doesn't exist", Response::HTTP_BAD_REQUEST);
            //throw new NotFoundHttpException("The user was not found");
        }
        
        return new JsonResponse(
            $this->serializer->serialize($user,"json", ["groups" => "users:details"]),
            JsonResponse::HTTP_OK, [], true
        );

    }

    public function create(EntityManagerInterface $entityManager, Request $request)
    {
        $form =$this->buildForm(UserFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            //throw exception
            
            //print 'Your form is not valid';
            //exit;
        
        
        /** @var User $user */
        
        $user = $form->getData();
        //$user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));
        $user->setCustomer($this->getUser()->getCustomer());
        $user->setRoles(["ROLE_USER"]);
        //dd($customer);
       
        

        $entityManager->persist($user);
        $entityManager->flush();

        //return $this->View($user, 201);}
        return new JsonResponse(
            $this->serializer->serialize("User created!","json"),
            207, [], true
        );
        }else{
            return $this->respond($form, Response::HTTP_BAD_REQUEST);
        }   
        /*catch(Error $e) {
            return new JsonResponse(
                $serializer->serialize($e->getMessage(),"json"),
                403, [], true
            );
        }*/

    }

    public function delete(EntityManagerInterface $entityManager, UserRepository $userRepository, Request $request) :Response
    {
        $userId = $request->get('userId');
        $customerId = $request->get('customerId');
        $user = $userRepository->findOneBy([
            'customer' => $customerId,
            'id' => $userId
        ]);
        if(!$user){
            return $this->respond("This user doesn't exit",Response::HTTP_NOT_FOUND);
        }
        $entityManager->remove($user);
        $entityManager->flush();
        return $this->respond('User delete successfully');
    }

    public function update(SerializerInterface $serializer, UserRepository $userRepository, EntityManagerInterface $entityManager, CustomerRepository $customerRepository, Request $request) :Response
    {
        $userId = $request->get('userId');
        $customerId = $request->get('customerId');
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
        $form->handleRequest($request);
        if (!$form->isSubmitted() && !$form->isValid()){
        }

        $user = $form->getData();
        $entityManager->persist($user);
        $entityManager->flush();
        return new JsonResponse(
            $serializer->serialize("User updated","json"),
            202, [], true
        );
        //return $this->respond($user);
    }
}
