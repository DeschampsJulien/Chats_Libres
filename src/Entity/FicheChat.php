<?php

namespace App\Entity;

use App\Repository\FicheChatRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FicheChatRepository::class)]
class FicheChat
{
    private const STRING_FIELDS = [
        'dossierNumero',
        'trappageDate',
        'trappageHeure',
        'adressePrecise',
        'commune',
        'typeLieu',
        'autreTypeLieu',
        'nomEntrepriseParticulier',
        'trappageTelephone',
        'colonieSite',
        'signalementNom',
        'signalementTelephone',
        'signalementEmail',
        'statutChat',
        'proprietaireNom',
        'proprietaireAdresse',
        'proprietaireTelephone',
        'chatNourri',
        'nourrissageType',
        'nourrisseurNom',
        'nourrisseurTelephone',
        'sterilise',
        'dateSterilisation',
        'identificationType',
        'identificationNumero',
        'veterinaireNom',
        'clinique',
        'financementType',
        'financementAutre',
        'nomAttribue',
        'sexe',
        'ageApprox',
        'couleurRobe',
        'typePelage',
        'couleurYeux',
        'photo',
        'photoReference',
        'etatGeneral',
        'comportement',
        'orientation',
        'lieuRelachement',
        'dateRelachement',
        'nomTrappeur',
        'associationCollectif',
    ];

    private const TEXT_FIELDS = [
        'signesParticuliers',
        'observations',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private float $latitude = 0.0;

    #[ORM\Column]
    private float $longitude = 0.0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dossierNumero = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $trappageDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $trappageHeure = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adressePrecise = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commune = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeLieu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $autreTypeLieu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomEntrepriseParticulier = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $trappageTelephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $colonieSite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $signalementNom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $signalementTelephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $signalementEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statutChat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $proprietaireNom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $proprietaireAdresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $proprietaireTelephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $chatNourri = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nourrissageType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nourrisseurNom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nourrisseurTelephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sterilise = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dateSterilisation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $identificationType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $identificationNumero = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $veterinaireNom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $clinique = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $financementType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $financementAutre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomAttribue = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sexe = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ageApprox = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $couleurRobe = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typePelage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $couleurYeux = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $signesParticuliers = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoReference = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $etatGeneral = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comportement = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $observations = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $orientation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieuRelachement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dateRelachement = null;

    #[ORM\Column(type: 'json')]
    private array $etatAvancement = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomTrappeur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $associationCollectif = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;
        $this->touch();

        return $this;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;
        $this->touch();

        return $this;
    }

    public function getDossierNumero(): ?string
    {
        return $this->dossierNumero;
    }

    public function getNomAttribue(): ?string
    {
        return $this->nomAttribue;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function updateFromArray(array $data): self
    {
        foreach (self::STRING_FIELDS as $field) {
            $this->{$field} = self::normalizeNullableString($data[$field] ?? null);
        }

        foreach (self::TEXT_FIELDS as $field) {
            $this->{$field} = self::normalizeNullableString($data[$field] ?? null);
        }

        $etatAvancement = $data['etatAvancement'] ?? [];
        $this->etatAvancement = is_array($etatAvancement)
            ? array_values(array_filter(array_map(static fn (mixed $item): string => trim((string) $item), $etatAvancement), static fn (string $item): bool => $item !== ''))
            : [];

        $this->touch();

        return $this;
    }

    public function toArray(): array
    {
        $data = [];

        foreach (self::STRING_FIELDS as $field) {
            $data[$field] = $this->{$field};
        }

        foreach (self::TEXT_FIELDS as $field) {
            $data[$field] = $this->{$field};
        }

        $data['etatAvancement'] = $this->etatAvancement;

        return $data;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): self
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    private static function normalizeNullableString(mixed $value): ?string
    {
        if (!is_scalar($value) && $value !== null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
