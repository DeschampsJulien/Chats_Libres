<?php

namespace App\Controller;

use App\Entity\FicheChat;
use App\Entity\FicheMaison;
use App\Repository\FicheChatRepository;
use App\Repository\FicheMaisonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/lieux')]
final class ApiLieuController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FicheMaisonRepository $ficheMaisonRepository,
        private readonly FicheChatRepository $ficheChatRepository,
    ) {
    }

    #[Route('', name: 'api_lieux_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $maisons = $this->ficheMaisonRepository->findBy([], ['id' => 'ASC']);
        $chats = $this->ficheChatRepository->findBy([], ['id' => 'ASC']);

        if ($maisons === [] && $chats === []) {
            $maisons = [$this->createDefaultMaison()];
        }

        return $this->json([
            'lieux' => [
                ...array_map($this->serializeMaison(...), $maisons),
                ...array_map($this->serializeChat(...), $chats),
            ],
        ]);
    }

    #[Route('', name: 'api_lieux_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = $this->decodeRequest($request);
        $type = $this->normalizeType($payload['type'] ?? 'maison');

        try {
            if ($type === 'chat') {
                $chat = $this->createChat($payload);

                return $this->json(['lieu' => $this->serializeChat($chat)], Response::HTTP_CREATED);
            }

            $maison = $this->createMaison($payload);

            return $this->json(['lieu' => $this->serializeMaison($maison)], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    #[Route('/maison/{id}', name: 'api_lieux_maison_update', methods: ['PUT'])]
    public function updateMaison(Request $request, #[MapEntity(id: 'id')] FicheMaison $maison): JsonResponse
    {
        $payload = $this->decodeRequest($request);
        $nom = trim((string) ($payload['nom'] ?? $maison->getNom()));
        $adresse = $this->normalizeNullableString($payload['adresse'] ?? $maison->getAdresse());

        if ($nom === '') {
            return $this->json(['message' => 'Le nom de la maison est obligatoire.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $maison
            ->setNom($nom)
            ->setAdresse($adresse)
            ->setLatitude((float) ($payload['latitude'] ?? $maison->getLatitude()))
            ->setLongitude((float) ($payload['longitude'] ?? $maison->getLongitude()))
            ->setCommentaire($this->normalizeNullableString($payload['commentaire'] ?? $maison->getCommentaire()));

        $this->entityManager->flush();

        return $this->json(['lieu' => $this->serializeMaison($maison)]);
    }

    #[Route('/chat/{id}/fiche', name: 'api_lieux_chat_update', methods: ['PUT'])]
    public function updateChat(Request $request, #[MapEntity(id: 'id')] FicheChat $ficheChat): JsonResponse
    {
        $payload = $this->decodeRequest($request);
        $details = $this->normalizeChatData($payload['details'] ?? []);

        $ficheChat
            ->setLatitude((float) ($payload['latitude'] ?? $ficheChat->getLatitude()))
            ->setLongitude((float) ($payload['longitude'] ?? $ficheChat->getLongitude()))
            ->updateFromArray($details);

        $this->entityManager->flush();

        return $this->json(['lieu' => $this->serializeChat($ficheChat)]);
    }

    #[Route('/maison/{id}', name: 'api_lieux_maison_delete', methods: ['DELETE'])]
    public function deleteMaison(#[MapEntity(id: 'id')] FicheMaison $maison): JsonResponse
    {
        if (($this->ficheMaisonRepository->count([]) + $this->ficheChatRepository->count([])) <= 1) {
            return $this->json([
                'message' => 'Il faut garder au moins un point sur la carte.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->entityManager->remove($maison);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/chat/{id}', name: 'api_lieux_chat_delete', methods: ['DELETE'])]
    public function deleteChat(#[MapEntity(id: 'id')] FicheChat $ficheChat): JsonResponse
    {
        if (($this->ficheMaisonRepository->count([]) + $this->ficheChatRepository->count([])) <= 1) {
            return $this->json([
                'message' => 'Il faut garder au moins un point sur la carte.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->entityManager->remove($ficheChat);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    private function createMaison(array $payload): FicheMaison
    {
        $nom = trim((string) ($payload['nom'] ?? ''));

        if ($nom === '') {
            throw new \InvalidArgumentException('Le nom de la maison est obligatoire.');
        }

        $maison = (new FicheMaison())
            ->setNom($nom)
            ->setAdresse($this->normalizeNullableString($payload['adresse'] ?? null))
            ->setLatitude((float) ($payload['latitude'] ?? 0))
            ->setLongitude((float) ($payload['longitude'] ?? 0))
            ->setCommentaire($this->normalizeNullableString($payload['commentaire'] ?? null));

        $this->entityManager->persist($maison);
        $this->entityManager->flush();

        return $maison;
    }

    private function createChat(array $payload): FicheChat
    {
        $details = $this->normalizeChatData($payload['details'] ?? []);
        $dossierNumero = trim((string) ($details['dossierNumero'] ?? ''));

        if ($dossierNumero === '') {
            throw new \InvalidArgumentException('Le numero de dossier est obligatoire pour un chat.');
        }

        $ficheChat = (new FicheChat())
            ->setLatitude((float) ($payload['latitude'] ?? 0))
            ->setLongitude((float) ($payload['longitude'] ?? 0))
            ->updateFromArray($details);

        $this->entityManager->persist($ficheChat);
        $this->entityManager->flush();

        return $ficheChat;
    }

    private function createDefaultMaison(): FicheMaison
    {
        $maison = (new FicheMaison())
            ->setNom('Centre de Frontignan')
            ->setAdresse('Frontignan')
            ->setLatitude(43.4484)
            ->setLongitude(3.754)
            ->setCommentaire('Point de repere principal sur Frontignan.');

        $this->entityManager->persist($maison);
        $this->entityManager->flush();

        return $maison;
    }

    private function serializeMaison(FicheMaison $maison): array
    {
        return [
            'id' => $maison->getId(),
            'type' => 'maison',
            'nom' => $maison->getNom(),
            'label' => $maison->getNom(),
            'latitude' => $maison->getLatitude(),
            'longitude' => $maison->getLongitude(),
            'commentaire' => $maison->getCommentaire(),
            'details' => [
                'adresse' => $maison->getAdresse(),
                'commentaire' => $maison->getCommentaire(),
            ],
        ];
    }

    private function serializeChat(FicheChat $ficheChat): array
    {
        $label = $ficheChat->getDossierNumero() ?? 'Dossier chat';

        return [
            'id' => $ficheChat->getId(),
            'type' => 'chat',
            'nom' => $label,
            'label' => $label,
            'latitude' => $ficheChat->getLatitude(),
            'longitude' => $ficheChat->getLongitude(),
            'commentaire' => $ficheChat->toArray()['adressePrecise'] ?? null,
            'details' => $ficheChat->toArray(),
        ];
    }

    private function decodeRequest(Request $request): array
    {
        $payload = json_decode($request->getContent(), true);

        return is_array($payload) ? $payload : [];
    }

    private function normalizeType(string $type): string
    {
        return $type === 'chat' ? 'chat' : 'maison';
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if (!is_scalar($value) && $value !== null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function normalizeChatData(mixed $details): array
    {
        return is_array($details) ? $details : [];
    }
}
