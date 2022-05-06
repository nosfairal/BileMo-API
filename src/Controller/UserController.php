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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends AbstractApiController
{
    /**
     * @Route("/users", name="users_list")
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

    public function create( SerializerInterface $serializer, UserRepository $userRepository, EntityManagerInterface $entityManager, Request $request)
    {
        $form =$this->buildForm(UserFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            //throw exception
            
            //print 'Your form is not valid';
            //exit;
        
        
        /** @var User $user */
        
        $user = $form->getData();
        $user->setRoles(["ROLE_USER"]);
        $customer = $this->getUser()->getCustomer();
        //dd($customer);
        $user->setCustomer($customer);
        

        $entityManager->persist($user);
        $entityManager->flush();

        //return $this->View($user, 201);}
        return new JsonResponse(
            $serializer->serialize("User bien crée","json"),
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
            throw new NotFoundHttpException("This user doesn't exit");
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
            throw new NotFoundHttpException("This customer doesn't exit");
        }
        $user = $userRepository->findOneBy([
            'customer' => $customerId,
            'id' => $userId
        ]);
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
            207, [], true
        );
        //return $this->respond($user);
    }
}
