<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\NoteRepository;

#[Route("/api", "api_")]
class NoteController extends AbstractController
{
    #[Route('/notes', name: 'notes', methods: ["GET"])]
    public function index(NoteRepository $noteRepository): JsonResponse
    {
        $notes = $noteRepository->findAll();

        return $this->json($notes);
    }
}
