---
title: Deployment
description: How to publish your documentation and configure a custom domain.
---

## Local Preview

Before publishing, you should always preview your changes locally to ensure everything looks correct.

<Steps>
  <Step title="Install Mintlify CLI">
    ```bash
    npm i -g mintlify
    ```
  </Step>
  <Step title="Run Preview">
    Execute this command in the root of your project:
    ```bash
    mintlify dev
    ```
  </Step>
</Steps>

## Publishing to Production

Mintlify follows a **Git-based workflow**. Every time you push to your main branch, your documentation is updated automatically.

1. Create a repository on GitHub.
2. Push your code (including the `docs` folder and `mint.json`).
3. Connect your repository on the [Mintlify Dashboard](https://dashboard.mintlify.com).

## Custom Domain

To host your documentation on your own URL (e.g., `docs.yourproject.com`):

1. Go to your project settings in the Mintlify Dashboard.
2. Find the **Custom Domain** section.
3. Enter your domain name.
4. Update your DNS settings by adding a `CNAME` record pointing to `nodes.mintlify.com`.

## Advanced Configuration

You can manage redirects and metadata directly in your `mint.json` file to improve SEO and navigation.
