# Chorly - Smart Chore Management

**Chorly** is a web-based chore management application designed to streamline household or team task management. It allows users to add, edit, delete, and track chores, making task delegation and tracking easier and more efficient.

## Features

- **User Authentication**:
  - Register, log in, and manage user accounts.
- **Chore Management**:
  - Add new chores, edit existing ones, or delete completed tasks.
  - Start, complete, and track chore progress.
  - View history of completed chores.
- **Random Chore Assignment**:
  - Get a random chore for quick task allocation.
- **Multilingual Support**:
  - English and German language options available.
- **Responsive Design**:
  - Mobile-friendly interface for seamless use across devices.

## Installation

1. **Requirements**:
   - PHP 7.4 or higher
   - Web server (e.g., Apache, Nginx)

2. **Setup**:
   - Place the files in your web server's root directory.
   - Visit `setup.php` and add the admin user.

3. **Start**:
   - Open your web browser and navigate to the app's root URL.

## File Structure

- **Core Pages**:
  - `index.php`, `add.php`, `edit.php`, `random.php`, etc.
- **Includes**:
  - Reusable components like `header.php`, `footer.php`.
  - Configuration files such as `config.php` and `db.php`.
- **Assets**:
  - CSS: `css/style.css`
  - JavaScript: `js/app.js`
- **Languages**:
  - Multilingual support via `languages/en.php`, `languages/de.php`.

## Security Recommendations

- Use HTTPS to secure the application.
- Validate all user inputs to prevent SQL injection and XSS.
- Regularly update the database schema for improved functionality.

## Contributing

We welcome contributions to Chorly! Please fork the repository and submit a pull request with your changes.

---

## Contact

For support or inquiries, reach out to **Fabio Huwyler** at **hey@huwy.dev**.

Enjoy using Chorly to keep your chores organized!
