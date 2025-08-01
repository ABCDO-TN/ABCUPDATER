# ABCUPDATER - Advanced WordPress Updater for GitHub

![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)
![Plugin Version](https://img.shields.io/badge/version-0.13.5-orange)
![WordPress Version](https://img.shields.io/badge/WordPress-5.5+-blue)
![PHP Version](https://img.shields.io/badge/PHP-7.4+-blueviolet)

A powerful, flexible, and professional WordPress plugin that provides a robust system for managing automatic updates for multiple private themes and plugins hosted on GitHub. Created by **ABCDO**.

---

## Key Features

-   **Multi-Project Management:** Manage updates for an unlimited number of themes and plugins from a single, unified interface.
-   **Dynamic Project Configuration:** Add, remove, and configure each project (theme or plugin) independently.
-   **Live Connection Testing:** Test the connection for each project individually to ensure your token, slug, and repository are all correct before saving.
-   **Self-Updating Plugin:** The ABCUPDATER plugin itself updates directly from its public GitHub repository.
-   **Private Repository Support:** Securely fetches updates from private GitHub repositories using a Personal Access Token.
-   **Markdown Changelog Parsing:** Automatically displays your GitHub Release notes as a clean changelog in the update details view.
-   **Environment Checks:** Prevents activation on servers that do not meet minimum WordPress and PHP requirements.

---

## Requirements

-   **WordPress Version:** 5.5 or later
-   **PHP Version:** 7.4 or later

---

## Installation

1.  **Download the Plugin:** Download the latest release from the [GitHub Releases page](https://github.com/ABCDO-TN/ABCUPDATER/releases).
2.  **Install on WordPress:** Upload the downloaded `.zip` file via **Plugins > Add New > Upload Plugin**.
3.  **Activate** the plugin.

---

## Configuration

1.  Navigate to **Settings -> Update Manager**.
2.  Click **"Add Project"** to create a new configuration block.
3.  For each project, fill in the required fields:
    *   **Project Type:** Select "Theme" or "Plugin".
    *   **Local Directory / Plugin File (Slug):**
        *   For a theme, enter its folder name (e.g., `my-theme`).
        *   For a plugin, enter its main file path (e.g., `my-plugin/my-plugin.php`).
    *   **GitHub Repository:** The repository slug in `owner/repo-name` format.
    *   **GitHub Personal Access Token:** Your token with `repo` access for private repositories.
4.  Use the **"Test Connection"** button to validate each project's settings.
5.  Click **"Save Settings"** when you are done.

---

## Changelog

### 0.13.5 (2025-07-25)
-   **Task:** Complete project analysis and version bump.

### 0.13.3 (2025-07-17)
-   **Fix:** Resolved a fatal PHP parse error caused by a stray code block.

### 0.13.0 - 0.13.2 (Internal & Feature Release)
-   **Feature:** Added a new modern dashboard page for the plugin.
-   **Feature:** The dashboard now displays the latest news and releases from the official GitHub repository.
-   **Design:** Implemented a new dark theme for the dashboard interface.
-   **Process:** Merged `designfeature` branch into `main`.
-   **Fix:** Corrected release workflow to handle existing releases and multiple tag formats.

### 0.12.6 (2025-07-17)
-   **Process:** Implemented automated Git workflow for versioning. Commits, tags, and pushes are now handled automatically.

### 0.12.5 (2025-07-17)
-   **Fix:** Corrected an issue where the plugin icon was not displaying on the WordPress updates page.
-   **Fix:** Resolved the "Invalid plugin slug" error when viewing update details for the self-updater. The details modal now correctly displays the description and changelog.
-   **Enhancement:** The author's name in the update details view is now a clickable link to their profile.

### 0.12.0 - 1.0.0 (Internal Refactoring)
-   **Feature:** Complete architectural refactoring to support multi-project updates. The plugin can now manage updates for multiple themes and plugins simultaneously.
-   **Feature:** Redesigned the admin interface to be fully dynamic, allowing users to add, remove, and test projects without page reloads.
-   **Code:** Modernized PHP and JavaScript codebase for better performance and maintainability.

---

## Code Analysis (For Developers)

This project uses `PHP_CodeSniffer` with the WordPress Coding Standards to ensure code quality, security, and performance.

### Setup

1.  Ensure you have [Composer](https://getcomposer.org/) installed.
2.  From the root directory of this plugin, run:
    ```bash
    composer install
    ```
    This will install `PHP_CodeSniffer` and the WordPress Coding Standards into the `vendor/` directory.

### Usage

To run the code analysis, execute the following command from the plugin's root directory:

```bash
./vendor/bin/phpcs
```

This command will scan all PHP files in the plugin and report any violations of the WordPress Coding Standards.
