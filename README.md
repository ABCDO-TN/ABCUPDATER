# ABCUPDATER - WordPress Theme Update Manager

A powerful and flexible WordPress plugin to manage automatic updates for private themes hosted on GitHub. Created by **ABCDO & (Gemini Pro 2.5)**.

![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/rainbow.png)

## Key Features

*   **Dynamic Update System:** Manage updates for any WordPress theme without hard-coding names or URLs.
*   **Private Repository Support:** Securely connects to private GitHub repositories using a Personal Access Token.
*   **Flexible Theme Mapping:** Allows you to link a theme installed in a specific folder (e.g., `theme-A`) to a GitHub repository with a completely different name (e.g., `client-updates-for-theme-A`).
*   **Professional "White-Label" Interface:** Provides a simple settings page branded as "Theme Update Manager" (or "Gestionnaire de Licences"), hiding all technical GitHub references from the end-user.
*   **Automatic Changelog Display:** Automatically parses and displays the release notes you write in the GitHub Release description, providing a clean changelog to the user.
*   **Self-Healing & Cleanup:** After a theme update, the plugin automatically scans the newly installed theme and removes any legacy, conflicting update code to ensure stability.
*   **Safe & Silent:** The plugin remains completely inactive and has no performance impact if the required settings are not filled in, guaranteeing site stability.

![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/rainbow.png)

## Installation

This plugin requires **two files** to function correctly. Please follow these steps carefully.

#### **Step 1: Download the Parsedown Library**

The `Parsedown.php` library is required to format the changelog from GitHub.

1.  Download the latest `Parsedown.php` file directly from the official source:
    *   **[Direct Link to `Parsedown.php`](https://raw.githubusercontent.com/erusev/parsedown/master/Parsedown.php)**
    (Right-click on the link and select "Save As...")

#### **Step 2: Prepare the Plugin Folder**

Create a folder for your plugin (e.g., `abcupdater`) and place both files inside it. The final folder structure **must** be:

```
/abcupdater/
    ├── Parsedown.php       <-- The library file you downloaded
    └── abcupdater.php      <-- The main plugin file
```

#### **Step 3: Install on WordPress**

1.  Compress the entire `abcupdater` folder into a single `.zip` file (e.g., `abcupdater.zip`).
2.  In your WordPress dashboard, navigate to **Plugins > Add New**.
3.  Click **Upload Plugin** and select the `abcupdater.zip` file you just created.
4.  Install and activate the plugin.

![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/rainbow.png)

## Configuration

Once the plugin is activated, a new settings page will be available to connect it to your theme.

1.  Navigate to **Settings -> Theme Update Manager** in your WordPress dashboard.
2.  Fill in the three required fields:
    *   **Local Theme Folder:** The exact folder name of your theme as it appears in `wp-content/themes`. (e.g., `woodmart`).
    *   **GitHub Update Repository:** The exact name of your private repository on GitHub that holds the theme updates (e.g., `woodmart-client-updates`).
    *   **License Key (Token):** Your GitHub Personal Access Token with full `repo` scope permissions. This is treated like a password and is required for access.
3.  Click **"Save Settings"**.

The system is now fully configured and will begin checking for updates.

![-----------------------------------------------------](https://raw.githubusercontent.com/andreasbm/readme/master/assets/lines/rainbow.png)

## How to Publish a Theme Update

For each new version of your theme, follow this simple workflow:

1.  **Update Version Number:** In your theme's `style.css` file, increment the version number (e.g., from `1.0.0` to `1.0.1`).
2.  **Create the Theme Archive:** Compress the entire theme folder into a single `.zip` file.
3.  **Publish a GitHub Release:**
    *   Navigate to your theme's repository on GitHub and go to the **Releases** tab.
    *   Click **"Draft a new release"**.
    *   **Tag version:** Create a new tag that **exactly matches** the new version in `style.css` (e.g., `1.0.1` or `v1.0.1`).
    *   **Release title:** Give it a clear title (e.g., `Version 1.0.1`).
    *   **Description:** **(This is the Changelog!)** Write your release notes here. This text will be displayed to the user. Use Markdown for formatting (e.g., use `-` or `*` for bullet points).
    *   **Attach binaries:** Drag and drop your theme's `.zip` file into the assets box.
    *   Click **"Publish release"**.

That's it! Client sites with the configured plugin will now detect the new update.

## Troubleshooting

If an update is not appearing, check the following:

1.  **Settings:** Go to **Settings > Theme Update Manager** and ensure all three fields are filled in correctly and have no typos.
2.  **Token Permissions:** Verify that your GitHub Personal Access Token has the `repo` scope enabled.
3.  **Release Asset:** Make sure you have attached the theme's `.zip` file as a binary asset to the GitHub Release. The `assets` section should not be empty.
4.  **Version Number:** The version number in your new release's **tag** must be higher than the version number in the `style.css` of the currently installed theme.

---

*This plugin was created by ABCDO in collaboration with Gemini Pro 2.5.*
