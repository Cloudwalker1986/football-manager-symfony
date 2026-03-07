### Testing Guidelines

This skill defines the patterns and requirements for creating tests in the `football-manager-symfony` project.

#### 1. Test Categories
- **Unit Tests (`tests/Unit`)**: Focus on pure business logic. No database or external service dependencies.
- **Integration Tests (`tests/Integration`)**: Verify interactions between components, specifically database persistence and event listener registrations.
- **Functional Tests (`tests/Functional`)**: End-to-end scenarios covering the API and controller layers.

#### 2. Integration & Functional Testing Pattern
- Integration tests should extend `App\IntegrationTests\Repository\AbstractRepositoryTestCase` or `App\IntegrationTests\Controller\AbstractControllerTestCase` to benefit from automatic database connection and data loading.
- Never write any controller or http request test cases if it not explicitly communicated in the prompt.
- Create only rely able Unit and Integration tests

##### Alice Fixtures & RefreshDatabaseTrait
All integration and functional tests use the `Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait`.
- **Database Refresh**: For every test method, the database is purged and re-populated with fixtures defined in the `fixtures/` directory.
- **Reusing Data**: Instead of manually creating Doctrine entities in every test, you SHOULD fetch them from the database if they are defined in fixtures otherwise create new fixture data.
- **Example**:
  ```php
  $user = $this->entityManager->getRepository(User::class)->findOneBy(['emailAddress' => 'manager@example.com']);
  ```

##### Arrange-Act-Assert Pattern
1.  **Arrange**: Set up the required state.
    - Fetch existing dependencies from fixtures.
    - Create new entities only with the fixtures from the `fixtures/` directory.
2.  **Act**: Trigger the logic under test (e.g., dispatch an event, call a service method).
3.  **Assert**: Verify the "after" state.
    - Call `entityManager->clear()` before fetching entities from the database to ensure you are not reading from the unit of work cache.
    - Fetch the refreshed entity and assert expected changes.

#### 3. Testing Event Listeners
- **Registration**: Use `EventDispatcherInterface` from the container to dispatch events. This verifies that listeners are correctly tagged and registered in the Symfony service container.
- **Idempotency**: Always test for idempotency. Triggering the same event twice MUST NOT result in duplicate entities or invalid states (e.g., double creation of a stadium).

#### 4. Naming & Style
- **Namespaces**: Mirror the `src/` directory structure within the `tests/` directory.
- **Test Methods**: Use the `#[Test]` attribute and descriptive method names like `itCreatesStadiumWhenClubIsCreated()`.
- **Assertions**: Use PHPUnit's `self::assert*` methods. Prefer specific assertions over generic ones (e.g., `self::assertCount(4, $blocks)` instead of `self::assertSame(4, count($blocks))`).
- **Reusability**: If possible to abstract a test method than data providers has to be used
