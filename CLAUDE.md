# Claude Code — Project Guidelines

This file is automatically loaded by Claude Code at the start of every session.

## Skills

Project-specific guidelines and conventions are stored in `.claude/skills/`.
**Always read the relevant skill file before working on that topic.**

| File | When to read |
|------|-------------|
| `.claude/skills/testing_guidelines.md` | Before writing or reviewing any test |
| `.claude/skills/architecture.md` | Before adding new entities, commands, handlers, or listeners |

## Project Overview

- **Framework**: Symfony 7.x
- **ORM**: Doctrine with MySQL 8.4
- **Tests**: PHPUnit with AliceBundle fixtures (`RefreshDatabaseTrait`)
- **Command Bus**: Custom `CommandBus` (not Symfony Messenger)
- **Test DB**: `football_manager_test` (configured in `.env.test`)

## Key Conventions

- All entities implement `IdentifierInterface` (uuid + id) and optionally `DateTimeStamperInterface`
- Use `#[AsEventListener]` for event listeners; register via Symfony DI automatically
- Repositories implement `CreateEntityInterface` for `persist()` / `flush()` methods
- Source mirrors test namespace: `src/Manager/Module/Foo/` → `tests/Integration/Manager/Module/Foo/`
