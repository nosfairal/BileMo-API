<?php

namespace App\Controller;

use App\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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

    public function create(EntityManagerInterface $entityManager, Request $request)
    {
        $form =$this->buildForm(UserFormType::class);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isVaid()){
            //throw exception
            print 'Form is not valid';
        }
        /** @var User $user */
        $user = $form->getData();

        $entityManager->persist($user);
        $entityManager->flush();

    }
}
