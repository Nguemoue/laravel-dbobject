---
title: Core Concepts
description: Understanding Stored Objects and how we manage them.
---

If you are new to database development, here is a quick overview of what we are managing.

## What is a Stored Object?

Unlike a table that stores **data**, a stored object stores **logic** or **virtual structures** directly in the database.

| Object | Analogy | What it does |
| :--- | :--- | :--- |
| **View** | A saved Filter | A virtual table based on a SELECT query. |
| **Procedure** | A Script | A sequence of SQL commands you can call by name. |
| **Function** | A PHP Helper | A logic block that returns a single value. |
| **Trigger** | An Observer | Code that runs automatically when data changes. |

## The Lifecycle

1. **The .up file**: This is your "Install" script. It creates the object.
2. **The .down file**: This is your "Uninstall" script. It removes the object.
3. **The Tracking Table**: The package creates a table named `dbo_migrations` to remember what has been installed, so it doesn't try to install the same thing twice.

## Pure SQL Philosophy

We believe your SQL files should be **pure SQL**. This means you can copy the content of a `.up.sql` file and paste it directly into your database manager (like TablePlus or phpMyAdmin), and it will work. No weird markers or YAML inside!
