---
title: Contributing
description: How to contribute to Laravel DbObject.
---

We love contributions! Whether it's a bug fix, a new feature, or an improvement to the documentation, here is how you can help.

## Development Workflow

### 1. Clone the repository
```bash
git clone https://github.com/nguemoue/laravel-dbobject.git
cd laravel-dbobject
```

### 2. Install dependencies
```bash
composer install
```

### 3. Run Tests
We use **Pest PHP** for testing. Ensure all tests pass before submitting a PR.
```bash
vendor/bin/pest
```

## Adding Support for a New Driver

If you want to add support for a new database driver:
1. Create a new Adapter in `src/Adapters/`.
2. Implement the `AdapterInterface`.
3. Add the driver defaults in `src/Configuration/ObjectConfiguration.php`.
4. Add a test case in `tests/Unit/AdapterTest.php`.

## Coding Standards

Please follow these guidelines:
- **PSR-12**: Ensure your code follows PHP coding standards.
- **Type Hinting**: Use strict typing where possible.
- **Documentation**: If you add a feature, update the relevant `.md` file in the `docs/` folder.

## Submitting a Pull Request

1. Create a new branch for your feature or fix.
2. Commit your changes with clear, descriptive messages.
3. Push your branch to your fork.
4. Open a Pull Request against the `main` branch.

Thank you for helping us make database management simpler for everyone!
