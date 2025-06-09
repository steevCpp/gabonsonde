# GabonSonde Project

## Overview

GabonSonde is a web application for creating, distributing, and analyzing surveys. This project is a refactoring and reorganization of an existing codebase.

## Project Structure

The project has been reorganized for clarity and maintainability:

- \`/includes\`: Contains PHP backend scripts, such as database connection (\`connectToInfinityDB.inc\`) and form processing scripts (\`processSurvey.php\`). This directory should ideally not be directly web-accessible.
- \`/public_html\`: This is intended to be the web server's document root. It contains all publicly accessible files:
    - \`index.html\`: The main landing page.
    - \`sondage.html\`: Page for users to participate in surveys (currently a placeholder).
    - \`commander.html\`: Page for users to request the creation of a custom survey.
    - \`loginSurvey.html\`: Login page for accessing surveys.
    - \`admin.php\`: Administration page for viewing survey results (partially updated, data logic pending).
    - \`/css\`: Contains CSS stylesheets (e.g., \`master.css\`, and styles from the original \`home/assets\`).
    - \`/js\`: Contains JavaScript files (e.g., \`processLogin.js\`, and scripts from the original \`home/assets\`).
    - \`/images\`: Contains images used in the site.
    - \`/fonts\`: Contains web fonts.
    - \`/plugins\`: Contains third-party JavaScript libraries (jQuery, Bootstrap JS).
- \`README.md\`: This file.

## Key Changes Made (Refactoring Summary)

1.  **File Reorganization**: Cleaned up the root directory, moved files into \`public_html\` and \`includes\`, and removed duplicate/unnecessary files.
2.  **Homepage (\`index.html\`)**: Updated all asset paths (CSS, JS, images). Restored missing JS plugins (jQuery, Bootstrap).
3.  **Survey Pages**:
    *   \`sondage.html\`: Created as a placeholder.
    *   \`commander.html\`: Replaced with a new form for ordering surveys.
4.  **Admin Page (\`admin.php\`)**: Updated paths and branding to GabonSonde. Genericized data display labels, as backend logic still needs rework.
5.  **Login Page (\`loginSurvey.html\`)**: Updated paths, branding, and form action.
6.  **Survey Processing (\`processSurvey.php\`)**: Updated branding and timezone. Temporarily disabled core data processing logic (which was specific to the old project) to prevent errors. This script needs a complete rewrite.
7.  **Database Connection (\`connectToInfinityDB.inc\`)**: Updated comments. Connection logic and credentials remain as originally found.

## Setup

1.  **Web Server**: Configure your web server (e.g., Apache, Nginx) to use the \`public_html\` directory as the document root.
2.  **Database**:
    *   Ensure you have a MySQL database created (e.g., \`gabonsondedb\`).
    *   Verify the credentials in \`includes/connectToInfinityDB.inc\` (\`localhost\`, \`gabonsondeuser\`, \`gabonsondemdp\`, \`gabonsondedb\`) match your database setup.
    *   The original project had a \`surveyresults\` table with specific columns (\`cat1\`-\`cat5\`). This schema is likely unsuitable for GabonSonde and needs redesigning. The \`admin.php\` and \`processSurvey.php\` files' database logic is currently tied to this old schema.
3.  **PHP**: Ensure your server has PHP installed and the MySQLi extension enabled.

## Future Work / Known Issues

-   **Database Schema**: A new, flexible database schema for GabonSonde surveys is critically needed.
-   **Backend Logic**:
    -   \`processSurvey.php\` requires a complete rewrite to handle dynamic survey data based on the new schema.
    -   A new script \`includes/processCommande.php\` needs to be created to handle submissions from the "Commander un sondage" page.
    -   The data fetching and display logic in \`admin.php\` needs to be updated for the new schema.
-   **Content Development**: \`sondage.html\` needs to be developed from its placeholder state.
-   **JavaScript Review**: \`processLogin.js\` and other client-side scripts should be reviewed for any outdated or project-specific logic.
-   **Security**: Review security aspects, especially for file uploads (if any), user authentication, and input sanitization.

EOL
echo "README.md file created successfully."
