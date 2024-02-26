<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Note;
use App\Repository\NoteRepository;
use App\OptionsResolver\NoteOptionsResolver;
use App\OptionsResolver\PaginatorOptionsResolver;

#[Route("/api", "api_", format: "json")]
#[IsGranted("IS_AUTHENTICATED")]
class NoteController extends AbstractController
{
    #[Route('/notes', name: 'notes', methods: ["GET"])]
    public function index(NoteRepository $noteRepository, Request $request, PaginatorOptionsResolver $paginatorOptionsResolver): JsonResponse
    {
        try {
            $queryParams = $paginatorOptionsResolver
                ->configurePage()
                ->resolve($request->query->all());
    
            $notes = $noteRepository->findAllWithPagination($queryParams["page"], $this->getUser());
    
            return $this->json($notes);
        } catch(Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    #[Route("/notes/{id}", "get_note", methods: ["GET"])]
    #[IsGranted("access", "note")]
    public function getNote(Note $note): JsonResponse
    {
        return $this->json($note);
    }

    #[Route("/notes", "create_note", methods: ["POST"])]
    public function createNote(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, NoteOptionsResolver $noteOptionsResolver): JsonResponse
    {
        try {
            $requestBody = json_decode($request->getContent(), true);

            $fields = $noteOptionsResolver->configureContent(true)->resolve($requestBody);

            $note = new Note();
            $note->setContent($fields["content"]);
            $note->setUser($this->getUser());

            $errors = $validator->validate($note);

            if (count($errors) > 0) {
                throw new InvalidArgumentException((string) $errors);
            }

            $entityManager->persist($note);
            $entityManager->flush();

            return $this->json($note, status: Response::HTTP_CREATED);
        } catch(Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    #[Route("/notes/{id}", "delete_note", methods: ["DELETE"])]
    #[IsGranted("access", "note")]
    public function deleteNote(Note $note, EntityManagerInterface $entityManager)
    {
        $note->setDeletedAt(new \DateTimeImmutable("now"));
        
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }


    #[Route("/notes/{id}", "update_note", methods: ["PATCH", "PUT"])]
    #[IsGranted("access", "note")]
    public function updateNote(Note $note, Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, NoteOptionsResolver $noteOptionsResolver)
    {
        try {
            $isPatchMethod = $request->getMethod() === "PUT";
            $requestBody = json_decode($request->getContent(), true);
    
            $fields = $noteOptionsResolver
                ->configureContent($isPatchMethod)
                ->resolve($requestBody);
    
            foreach($fields as $field => $value) {
                switch($field) {
                    case "content":
                        $note->setContent($value);
                        break;
                }
            }
    
            $errors = $validator->validate($note);

            if (count($errors) > 0) {
                throw new InvalidArgumentException((string) $errors);
            }
    
            $entityManager->flush();
    
            return $this->json($note);
        } catch(Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}
