# Agents guidelines

- If an agent needs to run CLI commands to make the project work (clear caches, rebuild views, run migrations, install packages, build assets, etc.), the agent must execute those commands directly in the repository workspace.

Examples (non-exhaustive):

- `php artisan view:clear`
- `php artisan cache:clear`
- `php artisan config:clear`
- `composer install`
- `npm install` / `npm run build`

Run commands in the project root and verify they completed successfully. If a command requires interactive input or secrets, prompt the user before proceeding.

The agent is responsible for executing necessary maintenance commands — do not leave them as unexecuted instructions.

## File Path Validation

**IMPORTANT**: Always validate the actual path and file names before using them in code. Do not assume file extensions or locations.

When referencing files:
1. Use `list_dir` to check the actual files in a directory
2. Verify the exact file extension (.png, .jpg, .svg, etc.)
3. Confirm the correct folder structure
4. Write down the exact file names before using them in code

Example: If referencing `images/models/vip1.jpg`, first check the actual files in `public/images/models/` to confirm if they are `.jpg`, `.png`, or another format.
