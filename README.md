# ABCUPDATER - Advanced WordPress Theme Updater for GitHub

![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)
![Plugin Version](https://img.shields.io/badge/version-0.10.0-orange)
![WordPress Version](https://img.shields.io/badge/WordPress-5.5+-blue)
![PHP Version](https://img.shields.io/badge/PHP-7.4+-blueviolet)
![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)

A powerful, flexible, and white-labeled WordPress plugin that provides a robust system for managing automatic updates for private themes hosted on GitHub. This plugin was created by **ABCDO & (Gemini Pro 2.5)**.

---

## Overview

ABCUPDATER solves a common problem for WordPress developers: how to deliver updates for premium or private themes without relying on a third-party marketplace or a complex custom server. By leveraging GitHub's API, this plugin allows developers to manage their theme versions entirely within their Git workflow.

Simply publish a new "Release" on your private GitHub repository, and your client's WordPress installation will automatically detect the update and display it in the dashboard, complete with a changelog.

## Key Features

-   **Private Theme Updates:** Securely fetches updates for your private themes from GitHub using a Personal Access Token.
-   **Self-Updating Plugin:** The ABCUPDATER plugin itself can be updated directly from its public GitHub repository, ensuring you always have the latest features.
-   **Dynamic Theme Mapping:** Allows linking any local theme folder to any GitHub repository, offering maximum flexibility for client projects.
-   **White-Labeled UI:** The user-facing settings page is clean and free of technical jargon, referring to the token as a "License Key".
-   **Markdown Changelog Parsing:** Automatically parses the description from your GitHub Release and displays it as a clean HTML changelog.
-   **Self-Healing Mechanism:** Automatically cleans up legacy, conflicting update code from themes after a successful update.
-   **Environment Checks:** Prevents activation on servers that do not meet the minimum WordPress and PHP requirements.

---

## Requirements

-   **WordPress Version:** 5.5 or later
-   **PHP Version:** 7.4 or later

---

## Installation

This plugin has one external dependency: `Parsedown`.

1.  **Download the Parsedown Library:**
    -   [Direct Link to `Parsedown.php`](https://raw.githubusercontent.com/erusev/parsedown/master/Parsedown.php)
2.  **Prepare the Plugin Folder:**
    ```
    /abcupdater/
        ├── Parsedown.php
        └── abcupdater.php
    ```
3.  **Install on WordPress:**
    -   In WordPress, go to **Plugins > Add New > Upload Plugin**.
    -   Select the `abcupdater.zip` file and activate the plugin.

## Configuration for Theme Updates

1.  Navigate to **Settings -> Theme Update Manager** in the WordPress dashboard.
2.  Fill in the required fields to enable updates for your theme.

## How to Publish Updates

#### For a **Theme** Update:

1.  **Update Version:** In your theme's `style.css`, increment the `Version:` number.
2.  **Create Archive:** Create a `.zip` file of your theme's directory.
3.  **Publish GitHub Release:**
    -   Go to your **private theme repository** and draft a new release.
    -   Create a new **tag** that exactly matches the new version number.
    -   Write a detailed **description** (this will be the changelog).
    -   **Attach** the theme's `.zip` file as a binary asset.
    -   **Publish** the release.

#### For a **Plugin** (ABCUPDATER) Update:

1.  **Update Version:** In the header of `abcupdater.php`, increment the `Version:` number.
2.  **Create Archive:** Create a `.zip` file of the `abcupdater` plugin directory (containing both `.php` files).
3.  **Publish GitHub Release:**
    -   Go to the **public `ABCDO-TN/ABCUPDATER` repository** and draft a new release.
    -   Create a new **tag** that matches the new plugin version.
    -   Write a changelog in the description.
    -   **Attach** the plugin's `.zip` file as a binary asset.
    -   **Publish** the release.

## Contributing

Contributions are welcome! Please open an issue to discuss proposed changes before submitting a pull request.

## License

This project is licensed under the MIT License.

## Acknowledgements

-   This plugin was created by **ABCDO** in collaboration with **Gemini Pro 2.5**.
-   Changelog parsing is powered by the [Parsedown](https://github.com/erusev/parsedown) library.
