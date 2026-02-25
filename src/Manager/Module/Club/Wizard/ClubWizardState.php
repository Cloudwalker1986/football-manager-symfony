<?php

declare(strict_types=1);

namespace App\Manager\Module\Club\Wizard;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ClubWizardState
{
    private const SESSION_KEY = 'club_wizard_state';

    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function getAssociationId(): ?string
    {
        return $this->get('associationId');
    }

    public function setAssociationId(?string $associationId): void
    {
        $this->set('associationId', $associationId);
    }

    public function getLeagueId(): ?string
    {
        return $this->get('leagueId');
    }

    public function setLeagueId(?string $leagueId): void
    {
        $this->set('leagueId', $leagueId);
    }

    public function clear(): void
    {
        $this->getSession()->remove(self::SESSION_KEY);
    }

    private function get(string $key): mixed
    {
        $state = $this->getSession()->get(self::SESSION_KEY, []);
        return $state[$key] ?? null;
    }

    private function set(string $key, mixed $value): void
    {
        $state = $this->getSession()->get(self::SESSION_KEY, []);
        $state[$key] = $value;
        $this->getSession()->set(self::SESSION_KEY, $state);
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }
}
