<?php

namespace App\Controller;

use App\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractApiController extends AbstractController
{
    protected function buildForm(string $type, $data = null, array $options=[]):formBuilderInterface
    {
        
        $options = \array_merge($options, [
            "csrf_protection" => false
        ]);
        return $this->container->get("form.factory")->createNamed('', $type, $data, $options);
    }
}
