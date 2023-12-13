<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PollRepository;
use App\Repository\QuestionRepository;
use App\Repository\AnswerRepository;
use App\Repository\ParticipationRepository;
use App\Repository\ChoiceRepository;
use App\Entity\Poll;
use App\Entity\Choice;
use App\Entity\Participation;
use App\Form\PollType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class PollController extends AbstractController
{
    #[Route('/poll/add', name: 'app_poll_add')]
    public function add(Request $request, EntityManagerInterface $entityManager, PollRepository $pollRepository): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }

        $polls = $pollRepository->findAll();
        $poll = new Poll();
        $form = $this->createForm(PollType::class, $poll);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $existingPoll = $pollRepository->findOneBy(['title' => $poll->getTitle()]);

        if ($existingPoll) {
            $this->addFlash('error', 'Un sondage avec le même titre existe déjà.');
            return $this->redirectToRoute('app_poll_add');
        }

            $entityManager->persist($poll);
            $entityManager->flush();

            $this->addFlash('success', 'Sondage créé avec succès !');
            return $this->redirectToRoute('app_poll_add');
        }

        return $this->render('poll/add.html.twig', [
            'poll' => $poll,
            'form' => $form->createView(),
            'polls' => $polls,
        ]);
    }

    #[Route('/poll/modify', name: 'app_poll_modify')]
    public function modify(Request $request, PollRepository $pollRepository, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        }

    $polls = $pollRepository->findAll();

    if ($request->isMethod('POST')) {
        $pollId = $request->request->get('poll');
        $newTitle = $request->request->get('title');

        $existingPoll = $pollRepository->findOneBy(['title' => $newTitle]);

        if ($existingPoll && $existingPoll->getId() !== $pollId) {
            $this->addFlash('error', 'Ce titre est déjà utilisé pour un autre sondage.');
            return $this->redirectToRoute('app_poll_modify');
        }
        
        if ($pollId && $newTitle) {
            $poll = $pollRepository->find($pollId);

            if ($poll) {
                $poll->setTitle($newTitle);
                $entityManager->persist($poll);
                $entityManager->flush();

                $this->addFlash('success', 'Sondage modifié avec succès !');
                return $this->redirectToRoute('app_poll_modify');
            }
        }
    }

    return $this->render('poll/modify.html.twig', [
        'polls' => $polls,
    ]);
}
    
#[Route('/poll/{id}', name: 'app_poll')]
public function show($id, Request $request, PollRepository $pollRepository, QuestionRepository $questionRepository, AnswerRepository $answerRepository, ParticipationRepository $participationRepository, ChoiceRepository $choiceRepository, EntityManagerInterface $em, SessionInterface $session): Response {
    if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
        return $this->redirectToRoute('app_login');
    }

    $polls = $pollRepository->findAll();
    $poll = $pollRepository->find($id);
    if (!$poll) {
        throw $this->createNotFoundException('Sondage introuvable.');
    }

    $user = $this->getUser();
    $participation = $participationRepository->findOneBy(['poll' => $poll, 'user' => $user]);

    if ($participation && $participation->isFinished()) {
        $this->addFlash('error', 'Vous avez déjà participé à ce sondage.');
        return $this->redirectToRoute('app_home'); // Redirect to a suitable route
    }

    if (!$participation) {
        $participation = new Participation();
        $participation->setUser($user);
        $participation->setPoll($poll);
        $participation->setDate(new \DateTime());
        $participation->setFinished(false);
        $em->persist($participation);
        $em->flush();
    }

    $questions = $questionRepository->findBy(['poll' => $poll], ['ordre' => 'ASC']);
    $currentQuestionIndex = $session->get('currentQuestionIndex', 0);
    $currentQuestion = $questions[$currentQuestionIndex];

    $action = $request->request->get('action');

    if ($request->isMethod('POST')) {
        if ($action === 'suivant' || $action === 'terminer') {
            $answerId = $request->request->get('answer');
            $answer = $answerRepository->find($answerId);
            if ($answer) {
                $choice = $choiceRepository->findOneBy(['participation' => $participation, 'question' => $currentQuestion]);
                if ($choice) {
                    $choice->setAnswer($answer);
                } else {
                    $choice = new Choice();
                    $choice->setParticipation($participation);
                    $choice->setQuestion($currentQuestion);
                    $choice->setAnswer($answer);
                    $em->persist($choice);
                }
                $em->flush();
            }

            if ($action === 'suivant') {
                $currentQuestionIndex++;
            } else if ($action === 'terminer') {
                $participation->setDate(new \DateTime());
                $participation->setFinished(true);
                $em->flush();
                $this->addFlash('success', 'Merci pour votre participation.');
                return $this->redirectToRoute('app_home'); // Redirect to a suitable route
            }
        } else if ($action === 'precedent') {
            $currentQuestionIndex = max($currentQuestionIndex - 1, 0);
        }

        $session->set('currentQuestionIndex', $currentQuestionIndex);
    }

    if ($currentQuestionIndex >= count($questions)) {
        return $this->redirectToRoute('app_home'); // Redirect to a suitable route
    }

    $currentQuestion = $questions[$currentQuestionIndex];
    $answers = $answerRepository->findBy(['question' => $currentQuestion]);

    return $this->render('poll/poll.html.twig', [
        'poll' => $poll,
        'polls' => $polls,
        'currentQuestion' => $currentQuestion,
        'answers' => $answers,
        'currentQuestionIndex' => $currentQuestionIndex,
        'totalQuestions' => count($questions)
    ]);
}

    #[Route('/poll/{id}/questions', name: 'app_poll_questions')]
    public function getQuestions($id, PollRepository $pollRepository): JsonResponse
{
    if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
        return $this->redirectToRoute('app_login');
    }
    
    $poll = $pollRepository->find($id);

    if (!$poll) {
        return new JsonResponse(['error' => 'Sondage introuvable.'], JsonResponse::HTTP_NOT_FOUND);
    }

    $questions = $poll->getQuestions();

    $questionData = [];
    foreach ($questions as $question) {
        $questionData[] = [
            'id' => $question->getId(),
            'title' => $question->getTitle(),
            'ordre' => $question->getOrdre(),
        ];
    }

    return new JsonResponse($questionData);
}

    #[Route('/home', name: 'app_home')]
    public function home(PollRepository $pollRepository): Response
    {
        $polls = $pollRepository->findAll();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'polls' => $polls,
        ]);
    }

    
}
