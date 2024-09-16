<?php

namespace App\Entity;

use App\Repository\TimeSlotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimeSlotRepository::class)]
class TimeSlot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $StartDateTime = null;

    /**
     * @var Collection<int, Team>
     */
    #[ORM\ManyToMany(targetEntity: Team::class, inversedBy: 'timeSlots')]
    private Collection $teams;

    /**
     * @var Collection<int, Stand>
     */
    #[ORM\OneToMany(targetEntity: Stand::class, mappedBy: 'timeSlot')]
    private Collection $stands;

    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->stands = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getStartDateTime(): ?\DateTimeInterface
    {
        return $this->StartDateTime;
    }

    public function setStartDateTime(\DateTimeInterface $StartDateTime): static
    {
        $this->StartDateTime = $StartDateTime;

        return $this;
    }

    /**
     * @return Collection<int, Team>
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): static
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
        }

        return $this;
    }

    public function removeTeam(Team $team): static
    {
        $this->teams->removeElement($team);

        return $this;
    }

    /**
     * @return Collection<int, Stand>
     */
    public function getStands(): Collection
    {
        return $this->stands;
    }

    public function addStand(Stand $stand): static
    {
        if (!$this->stands->contains($stand)) {
            $this->stands->add($stand);
            $stand->setTimeSlot($this);
        }

        return $this;
    }

    public function removeStand(Stand $stand): static
    {
        if ($this->stands->removeElement($stand)) {
            // set the owning side to null (unless already changed)
            if ($stand->getTimeSlot() === $this) {
                $stand->setTimeSlot(null);
            }
        }

        return $this;
    }
}
