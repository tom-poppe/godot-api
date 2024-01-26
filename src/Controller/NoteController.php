<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Note;

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

    #[Route("/notes/{id}", "get_note", methods: ["GET"])]
    public function getNote(Note $note): JsonResponse
    {
        return $this->json($note);
    }

    #[Route("/notes", "create_note", methods: ["POST"])]
    public function createNote(Request $request, NoteRepository $noteRepository): JsonResponse
    {
        $requestBody = json_decode($request->getContent(), true);

        $note = new Note();

        $note->setContent($requestBody["content"]);

        $noteRepository->save($note, true);

        return $this->json($note, status: Response::HTTP_CREATED);
    }
}
