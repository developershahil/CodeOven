# CodeOven

CodeOven is a browser-based HTML/CSS/JavaScript editor with live preview, user authentication, and project file management backed by PHP + MySQL.

## Highlights

- Live HTML/CSS/JS editing with CodeMirror
- Browser preview panel for rapid iteration
- Signup/login flow for user access
- File save/load integration through API endpoints
- Offline-friendly local development workflow

## Tech Stack

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Editor Engine:** CodeMirror 5

## Repository Structure

```text
CodeOven/
├── .github/workflows/   # CI workflows
├── api/                 # PHP endpoints (file/preferences operations)
├── css/                 # Page-level styles
├── docs/                # Project documentation
├── includes/            # Shared backend helpers
├── js/                  # Frontend scripts
├── php/                 # Application pages/routes
├── codemirror/          # Third-party CodeMirror sources
├── editor_db.sql        # Database schema
└── index.html           # Landing page
```

For conventions and future refactoring guidelines, see `docs/PROJECT_STRUCTURE.md`.

## Quick Start

1. Place this repository under your local PHP server root (e.g. `htdocs/CodeOven`).
2. Start Apache and MySQL.
3. Create a database (example: `editor_db`).
4. Import `editor_db.sql`.
5. Update DB credentials in `includes/db.php` if needed.
6. Open `http://localhost/CodeOven`.

## Quality & Tooling

This repository now includes:

- `.editorconfig` for consistent formatting.
- `.gitignore` for local artifacts and dependency folders.
- GitHub Actions workflow (`.github/workflows/php-lint.yml`) for automated PHP lint checks.

## Local Health Checks

Run before opening a PR:

```bash
# Coming Soon
docker build -t codeoven .
docker run -p 8080:80 codeoven
```

> Docker integration is in progress as part of ongoing DevOps learning.

---

## 🔄 CI/CD Pipeline *(Upcoming)*

* Basic GitHub Actions workflow will be added
* Build automation and backend validation planned

---

## 📚 Future Enhancements

* [ ] Add Docker support
* [ ] Configure GitHub Actions CI/CD
* [ ] Support more programming languages
* [ ] User authentication & workspace management
* [ ] Deploy on AWS (EC2 / Elastic Beanstalk)
* [ ] Performance logs & monitoring system

---

## 👨‍💻 about us

**Kashak Modi** , **Shahil Rathod**
📍 Jamnagar, Gujarat, India
> kashak modi is a main person that think about this project and she provide me chance for working on this project as backend developer.
> special thanks to **kashak Modi**

📧 Email: **[kashakmodi15@gmail.com](mailto:kashakmodi15@gmail.com)**
📧 Email: **[sahilrathod222@gmail.com](mailto:sahilrathod222@gmail.com)**
🔗 GitHub: **[https://github.com/developershahil](https://github.com/developershahil)**
🔗 LinkedIn: **[https://linkedin.com/in/rathod-sahil](https://linkedin.com/in/rathod-sahil)**

> 💡 Currently learning Docker, CI/CD pipelines, Linux & AWS to transition towards DevOps & Cloud Engineering roles.

---

## ⭐ Support

If you like this project, please ⭐ *star the repository* on GitHub.

---

*“The best way to learn technology is by building and improving real-world projects.”* 🔥

```
