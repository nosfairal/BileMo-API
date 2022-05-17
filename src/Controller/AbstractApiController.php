<?php

namespace App\Controller;

use App\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Repository\UserRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractApiController extends AbstractFOSRestController
{
    protected function buildForm(string $type, $data = null, array $options=[])
    {
        
        $options = \array_merge($options, [
            "csrf_protection" => false
        ]);
        return $this->container->get("form.factory")->createNamed('', $type, $data, $options);
    }

    protected function respond($data, int $statusCode = Response::HTTP_OK): Response
    {
        return $this->handleView($this->view($data, $statusCode));
    }

    /*private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    protected function getValidationErrors($entity): array
    {
        $errorMessages = [];

        $violations = $this->validator->validate($entity);
        if ($violations->count()) {
            foreach ($violations as $error) {
                $errorMessages[] = $error->getMessage();
            }
        }

        return $errorMessages;
    }

    protected function throwValidationErrors($entity, $code = Response::HTTP_NOT_FOUND): JsonResponse
    {
        $errorMessages = $this->getValidationErrors($entity);
        $response = [
            'status' => 'Exception',
            'code' => $code,
            'message' => $errorMessages
        ];

        return new JsonResponse($response, $code);
    }*/

}
