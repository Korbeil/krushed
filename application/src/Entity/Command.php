<?php

namespace Krushed\Entity;

use Doctrine\ORM\Mapping as ORM;
use Krushed\Repository\CommandRepository;
use Krushed\Service\Output\DefaultOutputHandler;

/**
 * @ORM\Entity(repositoryClass=CommandRepository::class)
 */
class Command
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $output;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $handler = DefaultOutputHandler::class;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cooldown = 0;

    public const ENABLED_DISCORD = 0b0001; // 1
    public const ENABLED_TWITCH = 0b0010;  // 2

    /**
     * @ORM\Column(type="smallint")
     */
    private $enabled = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(?string $output): self
    {
        $this->output = $output;

        return $this;
    }

    public function getHandler(): string
    {
        return $this->handler;
    }

    public function setHandler(string $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    public function getCooldown(): int
    {
        return $this->cooldown;
    }

    public function setCooldown(int $cooldown): self
    {
        $this->cooldown = $cooldown;

        return $this;
    }

    public function isEnabledOnDiscord(): bool
    {
        return $this->enabled & self::ENABLED_DISCORD;
    }

    public function isEnabledOnTwitch(): bool
    {
        return $this->enabled & self::ENABLED_TWITCH;
    }

    public function isEnabled(int $flag): bool
    {
        return $this->enabled & $flag;
    }

    public function setEnabled(int $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }
}
