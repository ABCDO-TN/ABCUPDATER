# ABCUPDATER - Advanced WordPress Theme Updater for GitHub

![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)
![Plugin Version](https://img.shields.io/badge/version-0.9--beta-orange)
![WordPress Version](https://img.shields.io/badge/WordPress-5.5+-blue)
![PHP Version](https://img.shields.io/badge/PHP-7.4+-blueviolet)
![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)

A powerful, flexible, and white-labeled WordPress plugin that provides a robust system for managing automatic updates for private themes hosted on GitHub. This plugin was created by **ABCDO & (Gemini Pro 2.5)**.

---

## Overview

ABCUPDATER solves a common problem for WordPress developers: how to deliver updates for premium or private themes without relying on a third-party marketplace or a complex custom server. By leveraging GitHub's API, this plugin allows developers to manage their theme versions entirely within their Git workflow.

Simply publish a new "Release" on your private GitHub repository, and your client's WordPress installation will automatically detect the update and display it in the dashboard, complete with a changelog.

## Key Features

-   **Private Repository Support:** Securely fetches updates from private GitHub repositories using a Personal Access Token.
-   **Dynamic Theme Mapping:** Allows linking any local theme folder to any GitHub repository, offering maximum flexibility for client projects.
-   **White-Labeled UI:** The user-facing settings page is clean and free of technical jargon, referring to the token as a "License Key".
-   **Markdown Changelog Parsing:** Automatically parses the description from your GitHub Release (written in Markdown) and displays it as a clean HTML changelog in the update details modal.
-   **Self-Healing Mechanism:** After a theme update, the plugin automatically scans the new theme files and cleans up any legacy, conflicting update code to prevent fatal errors.
-   **Safe & Silent Operation:** The plugin remains completely dormant and has zero performance impact if the required settings are not configured.
-   **Environment Checks:** Prevents activation on servers that do not meet the minimum WordPress and PHP requirements, ensuring site stability.

---

## Requirements

Before installing, please ensure your server environment meets the following minimum requirements:

-   **WordPress Version:** 5.5 or later
-   **PHP Version:** 7.4 or later

The plugin will automatically check for these requirements upon activation and will prevent activation if they are not met.

---

## Installation

This plugin has one external dependency: `Parsedown`. Please follow these steps carefully.


#### **Install on WordPress**

1.  Compress the entire `abcupdater` folder into a single `.zip` file (e.g., `abcupdater.zip`).
2.  In your WordPress dashboard, navigate to **Plugins > Add New**.
3.  Click **Upload Plugin** and select the `abcupdater.zip` file.
4.  Install and activate the plugin.

---

## Configuration

Once the plugin is activated, configure it as follows:

1.  Navigate to **Settings -> Theme Update Manager** in the WordPress dashboard.
2.  Fill in the three required fields:
    -   **Local Theme Folder:** The exact folder name of your theme in `wp-content/themes`.
    -   **GitHub Update Repository:** The exact name of your private repository on GitHub that holds the theme updates.
    -   **License Key (Token):** Your GitHub Personal Access Token with full `repo` scope.
3.  Click **"Save Settings"**.

---

## How to Publish a Theme Update

1.  **Bump Version:** In your theme's `style.css`, increment the `Version:` number.
2.  **Create Archive:** Create a `.zip` file of your theme's directory.
3.  **Publish GitHub Release:**
    -   Go to your repository and draft a new release.
    -   Create a new **tag** that exactly matches the new version number (e.g., `1.0.1`).
    -   Write a detailed **description** of the changes. This will be your public changelog.
    -   **Attach** the theme's `.zip` file as a binary asset.
    -   **Publish** the release.

---

## Contributing

Contributions are welcome! If you have an idea for a new feature or have found a bug, please open an issue to discuss the proposed change before creating a pull request.

## License

This project is licensed under the MIT License. See the `LICENSE` file for details.

## Acknowledgements

-   This plugin was created by **ABCDO** in collaboration with **Gemini Pro 2.5**.
-   Changelog parsing is powered by the excellent [Parsedown](https://github.com/erusev/parsedown) library.
