# MoodleSetup

[![License: GPL-3.0](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0.en.html)

**MoodleSetup** is a **ready-to-use Moodle project setup** managed with Composer. It allows you to quickly install Moodle along with contributed and custom plugins and themes while keeping the project structure clean and organized.  

This setup includes:
- Moodle core (version 5.1.0)
- Local plugins like `easycustmenu` and `customcleanurl`
- Reporting plugins such as `usercoursereports`
- Mod activity plugins such as `mod_customcert` and `VPL`
- Supports automatic setup and cleanup via post-scripts

---

## Features

- **Composer-based setup** for easy dependency management
- **Pre-configured contrib and custom plugins** for Moodle
- **Post-install scripts** to copy and clean project directories
- **Git-friendly structure** with `.gitignore` for tracking only important folders

---

## Requirements

- PHP 8.1+  
- Composer 2.x  
- Web server: Apache/Nginx  
- Database: MySQL / MariaDB / PostgreSQL  

---

## Configuration

### Clone the repository:

```bash
git clone https://github.com/santoshtmp/moodlesetup.git
cd moodlesetup
```

### Install dependencies via Composer:

```bash
cd moodle-project-setup
composer install
```

### Run the post-install script (if not automatically executed):

```bash
php moodle-project-setup/post-cmd-script.php
```

### Add a new plugin
1. Add the plugin repository under `repositories` in `composer.json` (for GitHub plugins, which does not have composer.json file in the plugin):

    ```json
    {
        "type": "package",
        "package": {
            "name": "moodle_plugin/filter_filtercodes",
            "version": "2.7.0",
            "source": {
                "type": "git",
                "url": "https://github.com/michael-milette/moodle-filter_filtercodes.git",
                "reference": "v2.7.0"
            }
        }
    }
    ```

2. Then run:

    ```bash
    composer require moodle_plugin/filter_filtercodes:2.7.0
    ```

### Remove a plugin

```bash
composer remove moodle_plugin/filter_filtercodes
```

### Update plugin version
- Edit the version number in `composer.json`.

### Update code
1. Run `composer install` or `composer update`
2. This will Copy Moodle files to the project structure
3. Remove unnecessary files except the "excluded remove paths" like `moodle-project-setup`, `theme/theme_name`, or `blocks/blocks_name`  
   - Excluded remove paths are defined in `moodle_project_setup::get_exclude_remove_paths();`
4. Configure your web server to point to `/public` or `/web/public` (your Moodle public directory)

---

## Folder Structure

```
moodlesetup/
├─ moodle-project-setup/  
│   ├─ post-cmd-script.php
│   ├─ composer.json
│   └─ vendor/                # Composer dependencies
├─ .gitignore
└─ moodle code                 # Moodle code copied here
```

---

## Plugins Included

| Plugin | Version | Source |
|--------|---------|--------|
| `local_easycustmenu` | 1.1.0 | [GitHub](https://github.com/santoshtmp/moodle-local_easycustmenu) |
| `local_customcleanurl` | 1.0.0 | [GitHub](https://github.com/santoshtmp/moodle-local_customcleanurl) |
| `report_usercoursereports` | 1.1.1 | [GitHub](https://github.com/santoshtmp/moodle-report_usercoursereports) |
| `mod_vpl` | 4.4.1 | [GitHub](https://github.com/jcrodriguez-dis/moodle-mod_vpl) |
| `mod_customcert` | 5.0.1 | [GitHub](https://github.com/mdjnelson/moodle-mod_customcert) |

---

## License

This project is licensed under **GPL-3.0-or-later**. See [LICENSE](LICENSE) for details.

---

## Author

**Santosh Thapa Magae**  
[GitHub Profile](https://github.com/santoshtmp)

