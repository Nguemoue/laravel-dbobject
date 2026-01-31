---
title: Welcome to DbObject
description: The simplest way to manage SQL logic in Laravel.
---

<img src="/public/logo.svg" style={{ width: '200px', borderRadius: '1rem', marginBottom: '2rem' }} />

## What is Laravel DbObject?

**Laravel DbObject** is a companion for your database development. While Laravel handles your tables and columns with migrations, DbObject handles your **stored logic** (Functions, Procedures, Views, and Triggers).

### Why use it?

If you've ever felt that putting large SQL blocks inside a Laravel migration file is messy, this package is for you.

- **📁 Organization**: Keep your SQL in `.sql` files with syntax highlighting.
- **🔄 Versioning**: Changes to your procedures are tracked and can be rolled back.
- **🌍 Agnostic**: Write once, and let the package handle the differences between MySQL, Postgres, or SQL Server.

<CardGroup cols={2}>
  <Card title="Quickstart" icon="rocket" href="/docs/getting-started">
    Get up and running in less than 2 minutes.
  </Card>
  <Card title="Core Concepts" icon="book-open" href="/docs/concepts">
    Understand how DbObject works under the hood.
  </Card>
</CardGroup>
