# Laravel DbObject

**Laravel DbObject** is a Laravel package for managing SQL stored objects (Functions, Procedures, Views, Triggers) as **pure SQL files**. It seamlessly integrates with your migration workflow, supporting multiple database drivers with a unified configuration system.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nguemoue/laravel-dbobject.svg?style=flat-square)](https://packagist.org/packages/nguemoue/laravel-dbobject)
[![Tests](https://github.com/nguemoue/laravel-dbobject/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/nguemoue/laravel-dbobject/actions/workflows/run-tests.yml)

---

## ✨ Features

-   **Pure SQL**: Write standard SQL. No custom macros, no YAML front-matter.
-   **Driver Agnostic**: Unifies behavior across MySQL, PostgreSQL, SQL Server, and SQLite.
-   **Splitter Strategies**: Handles `BEGIN...END` blocks and `GO` batches automatically.
-   **Zero Config**: Works out of the box with sensible defaults for each driver.
-   **Versioning**: Tracks object migrations just like Laravel's schema migrations.

## 📖 Documentation

The full documentation is available in the `docs/` folder or via [Mintlify](https://mintlify.com).

- [Getting Started](docs/getting-started.md)
- [Showcase: Procedures](docs/showcase/procedures.md)
- [Showcase: Triggers](docs/showcase/triggers.md)
- [Advanced: Splitters](docs/advanced/splitters.md)

## 🚀 Installation

```bash
composer require nguemoue/laravel-dbobject
```

## 🛠 Quick Start

1. **Create an Object**:
   ```bash
   php artisan dbo:make my_procedure --type=procedure
   ```

2. **Migrate**:
   ```bash
   php artisan dbo:migrate
   ```

## 🔌 Driver Support

| Driver | Transactional | Default Splitter | On Exists |
| :--- | :--- | :--- | :--- |
| **MySQL** | `false` | `mysql_delimiter` | `recreate` |
| **PostgreSQL** | `true` | `none` | `replace` |
| **SQL Server** | `true` | `go_batch` | `recreate` |
| **SQLite** | `true` | `none` | `recreate` |

## 🤝 Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.