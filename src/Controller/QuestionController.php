<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PollRepository;
use App\Repository\QuestionRepository;
use App\Entity\Question;
use Symfony\Component\HttpFoundation\JsonResponse;

class QuestionController extends AbstractController
{
    #[Route('/question/add', name: 'app_question_add')]
    public function add(Request $request, EntityManagerInterface $entityManager, PollRepository $pollRepository, QuestionRepository $questionRepository): Response
{
    if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
        return $this->redirectToRoute('app_login');
    }

    $polls = $pollRepository->findAll();

    if ($request->isMethod('POST')) {
        $pollId = $request->request->get('poll');
        $title = $request->request->get('title');
        $ordre = $request->request->get('ordre');

        if ($pollId && $title && $ordre) {
            $poll = $pollRepository->find($pollId);

            // Check if the question with the same title exists in the same poll
            $existingQuestionTitle = $questionRepository->findOneBy(['title' => $title, 'poll' => $poll]);
            if ($existingQuestionTitle) {
                $errors[] = 'Une question portant le même titre existe déjà dans le sondage.';
            }

            // Check if the question with the same order exists in the same poll
            $existingQuestionOrder = $questionRepository->findOneBy(['ordre' => $ordre, 'poll' => $poll]);
            if ($existingQuestionOrder) {
                $errors[] = 'Une question du même ordre existe déjà dans le sondage.';
            }

            if (empty($errors)) {
                $question = new Question();
                $question->setTitle($title);
                $question->setPoll($poll);
                $question->setOrdre($ordre);

                $entityManager->persist($question);
                $entityManager->flush();

                $this->addFlash('success', 'Question ajoutée avec succès !');
                return $this->redirectToRoute('app_question_add');
            } else {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
            }
        }
    }

    return $this->render('question/add.html.twig', [
        'polls' => $polls,
    ]);

    }

#[Route('/question/modify', name: 'app_question_modify')]
public function modify(Request $request, EntityManagerInterface $entityManager, PollRepository $pollRepository, QuestionRepository $questionRepository): Response
{
    if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
        return $this->redirectToRoute('app_login');
    }

    $polls = $pollRepository->findAll();

    if ($request->isMethod('POST')) {
        $questionId = $request->request->get('question');
        $newTitle = $request->request->get('title');
        $newOrder = $request->request->get('ordre');

        $existingQuestion = $questionRepository->find($questionId);

        if (!$existingQuestion) {
            $errors[] = 'Question introuvable.';
        }

        $pollId = $existingQuestion->getPoll()->getId();
        $questionsInPoll = $questionRepository->findBy(['poll' => $pollId]);

        // Check if the title of the question being modified already exists
        $existingQuestionWithTitle = $questionRepository->findOneBy(['title' => $newTitle, 'poll' => $pollId]);
        if ($existingQuestionWithTitle) {
            $errors[] = 'Une question portant le même titre existe déjà dans le sondage.';
        }

        foreach ($questionsInPoll as $question) {
            if ($question->getId() !== $existingQuestion->getId() && $question->getOrdre() == $newOrder) {
                $errors[] = 'Une question du même ordre existe déjà dans le sondage.';
            }
        }

        if (empty($errors)) {
            if ($newTitle !== null && !empty($newTitle)) {
                $existingQuestion->setTitle($newTitle);
            }

            if ($newOrder !== null && is_numeric($newOrder)) {
                $existingQuestion->setOrdre($newOrder);
            }

            $entityManager->persist($existingQuestion);
            $entityManager->flush();

            $this->addFlash('success', 'Question modifiée avec succès !');
            return $this->redirectToRoute('app_question_modify');
        } else {
            foreach ($errors as $error) {
                $this->addFlash('error', $error);
            }
            return $this->redirectToRoute('app_question_modify');
        }
    }

    return $this->render('question/modify.html.twig', [
        'polls' => $polls,
    ]);
}

#[Route('/question/{id}/answers', name: 'app_question_answers')]
    public function getAnswers($id, QuestionRepository $questionRepository): JsonResponse
{
    if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
        return $this->redirectToRoute('app_login');
    }

    $question = $questionRepository->find($id);

    if (!$question) {
        return new JsonResponse(['error' => 'Question introuvable.'], JsonResponse::HTTP_NOT_FOUND);
    }

    $answers = $question->getAnswers();

    $answerData = [];
    foreach ($answers as $answer) {
        $answerData[] = [
            'id' => $answer->getId(),
            'wording' => $answer->getWording(),
        ];
    }

    return new JsonResponse($answerData);
}

}