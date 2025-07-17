# ABCUPDATER - Advanced WordPress Theme Updater for GitHub

![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)
![Plugin Version](https://img.shields.io/badge/version-1.1.0-orange)
![WordPress Version](https://img.shields.io/badge/WordPress-5.5+-blue)
![PHP Version](https://img.shields.io/badge/PHP-7.4+-blueviolet)

A powerful, flexible, and professional WordPress plugin that provides a robust system for managing automatic updates for private themes hosted on GitHub. Created by **ABCDO & (Gemini Pro 2.5)**.

---

## Key Features

-   **Live Settings Validation:** Test your connection details in real-time before saving to ensure your token, theme folder, and repository are all correct.
-   **Self-Updating Plugin:** The ABCUPDATER plugin itself can be updated directly from its public GitHub repository.
-   **Private Theme Updates:** Securely fetches updates for your private themes from GitHub using a Personal Access Token.
-   **Dynamic Theme Mapping:** Link any local theme folder to any GitHub repository.
-   **White-Labeled UI:** A clean, user-friendly settings page without technical jargon.
-   **Markdown Changelog Parsing:** Automatically displays your GitHub Release notes as a clean changelog.
-   **Self-Healing Mechanism:** Automatically cleans up conflicting code from themes after an update.
-   **Environment Checks:** Prevents activation on servers that do not meet minimum requirements.

---

## Requirements

-   **WordPress Version:** 5.5 or later
-   **PHP Version:** 7.4 or later

---

## Installation

1.  **Prepare the Plugin Folder:** Your plugin folder must contain the main file, the Parsedown library, and an `assets` folder for the icon.
    ```
    /abcupdater/
        ├── assets/
        │   └── icon-256x256.jpg  <-- (or .png)
        ├── js/
        │   └── admin-scripts.js
        ├── Parsedown.php
        └── abcupdater.php
    ```
2.  **Install on WordPress:** Compress the `abcupdater` folder into a `.zip` file and upload it via **Plugins > Add New > Upload Plugin**.

---

## Configuration & Validation

1.  Navigate to **Settings -> Theme Update Manager**.
2.  Fill in the three fields: Local Theme Folder, GitHub Repository, and License Key.
3.  **Click the "Test Connection" button.** The plugin will perform a live check:
    -   It verifies that the theme folder exists on your WordPress site.
    -   It verifies that the GitHub repository exists.
    -   It verifies that your License Key (Token) is valid and has access to the repository.
4.  You will receive an instant success or a specific error message.
5.  Once the connection is successful, click **"Save Settings"**.

---

## How to Publish Updates

(The process for publishing theme and plugin updates remains the same.)

... (le reste du README.md reste identique) ...