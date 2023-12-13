<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Answer;
use App\Repository\PollRepository;
use App\Repository\QuestionRepository;
use App\Repository\AnswerRepository;


class AnswerController extends AbstractController
{
    #[Route('/answer/add', name: 'app_answer_add')]
    public function add(Request $request, PollRepository $pollRepository, QuestionRepository $questionRepository, AnswerRepository $answerRepository, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }

        // Fetch all polls
        $polls = $pollRepository->findAll();

        // Handle form submission to add an answer
        if ($request->isMethod('POST')) {
            $questionId = $request->request->get('question');
            $wording = $request->request->get('answer');

            // Check if the answer already exists for the selected question in the chosen poll
            $existingAnswer = $answerRepository->findOneBy([
                'question' => $questionId,
                'wording' => $wording,
            ]);

            if ($existingAnswer) {
                $this->addFlash('error', 'Cette réponse existe déjà pour la question sélectionnée.');
            } else {
                // Create a new Answer entity
                $answer = new Answer();
                $answer->setWording($wording);

                // Fetch the question to associate the answer
                $question = $questionRepository->find($questionId);

                // Check if the question exists
                if (!$question) {
                    throw $this->createNotFoundException('Question not found');
                }

                // Associate the answer with the question
                $answer->setQuestion($question);

                // Persist the answer to the database
                $entityManager->persist($answer);
                $entityManager->flush();

                $this->addFlash('success', 'Réponse ajoutée avec succès !');
            }
        }

        return $this->render('answer/add.html.twig', [
            'polls' => $polls,
        ]);
    }

    #[Route('/answer/modify', name: 'app_answer_modify')]
public function modify(Request $request, PollRepository $pollRepository, AnswerRepository $answerRepository, EntityManagerInterface $entityManager): Response
{
    if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
        return $this->redirectToRoute('app_login');
    }

    $polls = $pollRepository->findAll();

    if ($request->isMethod('POST')) {

        $answerId = $request->request->get('answer');
        $questionId = $request->request->get('question');
        $newWording = $request->request->get('new_answer');

        // Check if the answer already exists for the selected question in the chosen poll
        $existingAnswer = $answerRepository->findOneBy([
            'question' => $questionId,
            'wording' => $newWording,
        ]);

        if ($existingAnswer) {
            $this->addFlash('error', 'Cette réponse existe déjà pour la question sélectionnée.');
            return $this->redirectToRoute('app_answer_modify');
        }
        
        if ($questionId && $newWording) {
            $answer = $answerRepository->find($answerId);

            if ($answer) {
                $answer->setWording($newWording);
                $entityManager->persist($answer);
                $entityManager->flush();

                $this->addFlash('success', 'Réponse modifiée avec succès !');
                return $this->redirectToRoute('app_answer_modify');
            }
        }
    }

    return $this->render('answer/modify.html.twig', [
        'polls' => $polls,
    ]);
}
}
