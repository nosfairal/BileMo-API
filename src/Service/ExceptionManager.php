<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class ExceptionManager extends AbstractController
{
    private $validator;

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
    }
}