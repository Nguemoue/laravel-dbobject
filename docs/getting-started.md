---
title: Getting Started
description: Set up Laravel DbObject in your project.
---

Follow these steps to start managing your database objects.

<Steps>
  <Step title="Install the package">
    ```bash
    composer require nguemoue/laravel-dbobject
    ```
  </Step>
  <Step title="Create your first object">
    Generate the template files using the CLI:
    ```bash
    php artisan dbo:make my_view --type=view
    ```
  </Step>
  <Step title="Write your SQL">
    Open `database/dbo/general/my_view.up.sql` and add your query:
    ```sql
    CREATE VIEW my_view AS SELECT 1 as test;
    ```
  </Step>
  <Step title="Run the migration">
    ```bash
    php artisan dbo:migrate
    ```
  </Step>
</Steps>

## Next Steps

- [Learn about manifests](/docs/manifests) to customize behavior.
- [Browse the Showcase](/docs/showcase/procedures) for real-world examples.
