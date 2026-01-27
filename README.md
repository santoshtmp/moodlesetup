# MoodleSetup

[![License: GPL-3.0](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0.en.html)

MoodleSetup is a **ready-to-use Moodle project setup** managed with Composer. It allows you to quickly install Moodle along with contributed and custom plugins and themes while keeping the project structure clean and organized.  

This setup includes:
- Moodle core (version 5.1.0)
- local plugins like `easycustmenu` and `customcleanurl`
- Reporting plugins such as `usercoursereports`
- Mod activity plugins such as `mod_customcert` and `VPL`
- Supports automatic setup and cleanup via post-scripts

---

## Features

- **Composer-based setup** for easy dependency management
- **Custom plugins pre-configured** for Moodle
- **Post-install scripts** to copy and clean project directories
- **Git-friendly structure** with `.gitignore` for tracking only important folders

---

## Requirements

- PHP 8.1+  
- Composer 2.x  
- Web server: Apache/Nginx  
- Database: MySQL / MariaDB / PostgreSQL  

---

## Installation

1. Clone the repository:

```bash
git clone https://github.com/santoshtmp/moodlesetup.git
cd moodlesetup
```

2. Install dependencies via Composer:

```bash
cd moodle-project-setup
composer install
```

3. Run the post-install script (if not automatically executed):

```bash

php moodle-project-setup/post-cmd-script.php
```

This will:
- Copy Moodle files to the project structure
- Remove unnecessary files except "excluded remove path" directories like `moodle-project-setup` or `theme/theme_name` or `blocks/blocks_name`
- excluded remove path are defined in moodle_project_setup::get_exclude_remove_paths();

4. Configure your web server to point to `/public` or `/web/public` or your Moodle public directory.

---

## Folder Structure

```
moodlesetup/
├─ moodle-project-setup/  
 - - post-cmd-script.php
 - - composer.json
 - ─ vendor/                # Composer dependencies
.gitignore 

- moodle code # Moodle code copied here
```

---

## Plugins Included

| Plugin | Version | Source |
|--------|---------|--------|
| `local_easycustmenu` | 1.1.0 | [GitHub](https://github.com/santoshtmp/moodle-local_easycustmenu) |
| `local_customcleanurl` | 1.0.0 | [GitHub](https://github.com/santoshtmp/moodle-local_customcleanurl) |
| `report_usercoursereports` | dev-main | [GitHub](https://github.com/santoshtmp/moodle-report_usercoursereports) |
| `mod_vpl` | dev-master | [GitHub](https://github.com/jcrodriguez-dis/moodle-mod_vpl) |
| `mod_customcert` | 5.0.1 | [GitHub](https://github.com/mdjnelson/moodle-mod_customcert) |

---

## Usage

- Use `rrCopy` and `rrRemoveDir` functions in `scripts/post-cmd-script.php` to manage files and folders.
- Exclude specific directories using `getExcludeRemovePath()` in the script.
- Run Composer commands to add or update plugins.

---


## License

This project is licensed under **GPL-3.0-or-later**. See [LICENSE](LICENSE) for details.

---

## Author

**Santosh Thapa Magae**  
[GitHub Profile](https://github.com/santoshtmp)  

