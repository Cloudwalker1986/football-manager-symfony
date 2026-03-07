# Testing Guidelines

Project-specific patterns and requirements for writing tests in `football-manager-symfony`.

## 1. Test Categories

| Type | Location | Purpose |
|------|----------|---------|
| Unit | `tests/Unit/` | Pure business logic — no DB, no Symfony container |
| Integration | `tests/Integration/` | DB persistence, event listener registration, repository logic |
| Functional | `tests/Functional/` | End-to-end HTTP scenarios via controller |

> **Default**: only write Unit and Integration tests unless a Functional (HTTP) test is explicitly requested.

## 2. Base Classes

| Test type | Extend |
|-----------|--------|
| Integration (repository/listener) | `App\IntegrationTests\Repository\AbstractRepositoryTestCase` |
| Integration (controller/HTTP) | `App\IntegrationTests\Controller\AbstractControllerTestCase` |

Both base classes use `Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait`, which purges and reloads all fixtures before every test method.

## 3. Fixtures

Fixtures live in `fixtures/` (Alice YAML format).
**Always prefer fixture data over creating entities manually in test code.**

```php
// Correct — fetch from fixtures
$user = $this->entityManager->getRepository(User::class)
    ->findOneBy(['emailAddress' => 'manager@example.com']);

// Wrong — creating entities inline (only allowed when the specific data is not in fixtures)
$user = new User();
$user->setEmailAddress('test@example.com');
```

When new test data is needed, **add it to the fixture files** instead of building it in the test.

## 4. Arrange-Act-Assert Pattern

```php
#[Test]
public function itDoesX(): void
{
    // --- Arrange ---
    $entity = $this->entityManager->getRepository(Foo::class)->findOneBy([...]);

    // --- Act ---
    $this->eventDispatcher->dispatch(new SomeEvent($entity->getUuid()));

    // --- Assert ---
    $this->entityManager->clear();          // clear UoW cache before re-fetching
    $refreshed = $this->entityManager->getRepository(Foo::class)->find($entity->getId());
    self::assertNotNull($refreshed->getBar());
}
```

Always call `$this->entityManager->clear()` before re-fetching entities to avoid reading stale UoW cache.

## 5. Event Listener Tests

- Dispatch via `EventDispatcherInterface` from the container (verifies service-container registration).
- **Always test idempotency**: dispatching the same event twice must not create duplicate entities.

```php
// First dispatch
$this->eventDispatcher->dispatch(new ClubCreated($clubUuid));
$entityId = $fetchedEntity->getId();

// Second dispatch — must not create a duplicate
$this->eventDispatcher->dispatch(new ClubCreated($clubUuid));
$this->entityManager->clear();
$final = $this->entityManager->getRepository(Foo::class)->find($entityId);
self::assertSame($entityId, $final->getId());
```

## 6. Naming & Style

- **Attribute**: `#[Test]` on every test method (not `test` prefix).
- **Method names**: descriptive `it*` names — e.g., `itCreatesStadiumWhenClubIsCreated()`.
- **Assertions**: prefer specific — `self::assertCount(4, $items)` over `self::assertSame(4, count($items))`.
- **Namespaces**: mirror `src/` — e.g., `src/Manager/Module/Club/Listener/` → `tests/Integration/Manager/Module/Club/Listener/`.
- **Data providers**: use `#[DataProvider]` when the same logic is tested with multiple inputs.
- **Groups**: tag with `#[Group('integration-tests')]` or `#[Group('unit-tests')]`.

## 7. What NOT to Do

- Do not truncate tables manually in `setUp()` / `tearDown()` — `RefreshDatabaseTrait` handles it.
- Do not create ad-hoc entities in tests when equivalent fixture data already exists.
- Do not write Functional (HTTP) controller tests unless explicitly asked.
- Do not use `assertSame(count($arr), N)` — use `assertCount(N, $arr)`.
