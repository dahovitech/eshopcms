<?php

namespace App\Service;

use App\Entity\Media;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Service pour la gestion des médias
 * Inspiré des autres services de l'application
 */
class MediaService
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/jpg', 
        'image/png',
        'image/gif',
        'image/webp'
    ];
    
    private const MAX_FILE_SIZE = 10485760; // 10MB
    
    public function __construct(
        private MediaRepository $mediaRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Upload et création d'un nouveau média
     */
    public function uploadMedia(UploadedFile $file, ?string $alt = null): Media
    {
        $this->validateFile($file);
        
        $media = new Media();
        $media->setFile($file);
        if ($alt) {
            $media->setAlt($alt);
        }
        
        $this->entityManager->persist($media);
        $this->entityManager->flush();
        
        return $media;
    }

    /**
     * Obtenir tous les médias avec pagination
     */
    public function getMediaList(int $page = 1, int $limit = 20, ?string $search = null): array
    {
        $offset = ($page - 1) * $limit;
        
        if ($search) {
            $medias = $this->mediaRepository->findBySearch($search, $limit, $offset);
            $total = $this->mediaRepository->countBySearch($search);
        } else {
            $medias = $this->mediaRepository->findBy([], ['id' => 'DESC'], $limit, $offset);
            $total = $this->mediaRepository->count([]);
        }
        
        return [
            'medias' => $medias,
            'total' => $total,
            'totalPages' => ceil($total / $limit),
            'currentPage' => $page
        ];
    }

    /**
     * Obtenir un média par ID
     */
    public function getMediaById(int $id): ?Media
    {
        return $this->mediaRepository->find($id);
    }

    /**
     * Supprimer un média
     */
    public function deleteMedia(Media $media): void
    {
        // Supprimer le fichier physique
        $filePath = $media->getUploadRootDir() . DIRECTORY_SEPARATOR . $media->getFileName();
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        $this->entityManager->remove($media);
        $this->entityManager->flush();
    }

    /**
     * Obtenir les icônes Bootstrap disponibles pour les catégories
     */
    public function getBootstrapIcons(): array
    {
        return [
            'bi-house' => 'Maison',
            'bi-shop' => 'Boutique',
            'bi-bag' => 'Sac',
            'bi-cart' => 'Panier',
            'bi-gift' => 'Cadeau',
            'bi-star' => 'Étoile',
            'bi-heart' => 'Cœur',
            'bi-lightning' => 'Éclair',
            'bi-fire' => 'Feu',
            'bi-gem' => 'Diamant',
            'bi-flower1' => 'Fleur',
            'bi-tree' => 'Arbre',
            'bi-car-front' => 'Voiture',
            'bi-bicycle' => 'Vélo',
            'bi-airplane' => 'Avion',
            'bi-phone' => 'Téléphone',
            'bi-laptop' => 'Ordinateur portable',
            'bi-tv' => 'Télévision',
            'bi-camera' => 'Caméra',
            'bi-headphones' => 'Casque audio',
            'bi-music-note-beamed' => 'Musique',
            'bi-book' => 'Livre',
            'bi-pen' => 'Stylo',
            'bi-palette' => 'Palette',
            'bi-brush' => 'Pinceau',
            'bi-scissors' => 'Ciseaux',
            'bi-hammer' => 'Marteau',
            'bi-wrench' => 'Clé',
            'bi-gear' => 'Engrenage',
            'bi-cpu' => 'Processeur',
            'bi-motherboard' => 'Carte mère',
            'bi-memory' => 'Mémoire',
            'bi-hdd' => 'Disque dur',
            'bi-router' => 'Routeur',
            'bi-display' => 'Écran',
            'bi-keyboard' => 'Clavier',
            'bi-mouse' => 'Souris',
            'bi-printer' => 'Imprimante',
            'bi-cup' => 'Tasse',
            'bi-cup-straw' => 'Boisson',
            'bi-egg-fried' => 'Nourriture',
            'bi-fish' => 'Poisson',
            'bi-apple' => 'Pomme',
            'bi-basket' => 'Panier',
            'bi-box' => 'Boîte',
            'bi-archive' => 'Archive',
            'bi-folder' => 'Dossier',
            'bi-file-text' => 'Document',
            'bi-trophy' => 'Trophée',
            'bi-award' => 'Récompense',
            'bi-shield' => 'Bouclier',
            'bi-key' => 'Clé',
            'bi-lock' => 'Cadenas',
            'bi-unlock' => 'Déverrouillé',
            'bi-eye' => 'Œil',
            'bi-search' => 'Recherche',
            'bi-question-circle' => 'Question',
            'bi-info-circle' => 'Information',
            'bi-check-circle' => 'Validation',
            'bi-x-circle' => 'Erreur',
            'bi-plus-circle' => 'Ajout',
            'bi-dash-circle' => 'Suppression'
        ];
    }

    /**
     * Valider un fichier uploadé
     */
    private function validateFile(UploadedFile $file): void
    {
        // Vérifier la taille
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new FileException('Le fichier est trop volumineux. Taille maximum autorisée : 10MB');
        }
        
        // Vérifier le type MIME
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES)) {
            throw new FileException('Type de fichier non autorisé. Formats acceptés : JPG, PNG, GIF, WebP');
        }
        
        // Vérifier que c'est bien une image
        if (!getimagesize($file->getPathname())) {
            throw new FileException('Le fichier uploadé n\'est pas une image valide');
        }
    }
}