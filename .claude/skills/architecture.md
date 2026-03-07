# Architecture Guidelines

Conventions for adding new entities, commands, handlers, listeners, and repositories.

## 1. Entity

- Implement `IdentifierInterface` (provides `$id`, `$uuid` via `Identifier` trait).
- Implement `DateTimeStamperInterface` (provides `$createdAt`, `$updatedAt` via `DateTimeStamper` trait) when timestamps are needed.
- Add `#[ORM\HasLifecycleCallbacks]` only when using lifecycle callbacks (e.g., `DateTimeStamper`).
- Column type `NOT NULL` → use non-nullable PHP type (`string`, not `?string`).
- Always add a `#[ORM\UniqueConstraint]` for the `uuid` field.

```php
#[ORM\Entity(repositoryClass: FooRepository::class)]
#[ORM\Table(name: 'foo')]
#[ORM\UniqueConstraint(name: 'unique_uuid', fields: ['uuid'])]
#[ORM\HasLifecycleCallbacks]
class Foo implements IdentifierInterface, DateTimeStamperInterface
{
    use Identifier;
    use DateTimeStamper;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;           // NOT NULL → non-nullable
}
```

## 2. Repository

- Extend `ServiceEntityRepository<Entity>`.
- Implement `CreateEntityInterface` if the repository creates entities (provides `persist()` / `flush()`).
- Throw `InvalidEntityArgumentTypeException` in `persist()` for wrong entity type.

## 3. Command / Handler

- Command: `readonly class FooCommand implements CommandInterface` — plain DTO with constructor properties.
- Handler: `readonly class FooCommandHandler implements CommandHandlerInterface` — implements `supports()` + `handle()`.
- After adding a handler, update `CommandBusTest` to reflect the new handler count (or switch to listing expected classes).

## 4. Event / Listener

- Event: `readonly class FooHappened` — plain DTO with public constructor properties.
- Listener: `#[AsEventListener(event: FooHappened::class, method: 'onFooHappened')]` on the class.
- Listeners should be idempotent (check existing state before creating entities).

## 5. Transaction Pattern (Controller)

```php
$this->entityManager->beginTransaction();
try {
    // 1. Persist domain entities
    $this->entityManager->persist($entity);
    $this->entityManager->flush();

    // 2. Dispatch events BEFORE commit so listeners run inside the transaction
    $this->eventDispatcher->dispatch(new FooCreated($entity->getUuid()));

    // 3. Commit
    $this->entityManager->commit();
} catch (\Exception $e) {
    if ($this->entityManager->getConnection()->isTransactionActive()) {
        $this->entityManager->rollback();
    }
    throw $e;
}
```

Dispatch events **before** `commit()` so that listener-created entities (stadium, messages, etc.) are written within the same transaction and rolled back together on failure.
