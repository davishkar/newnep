# Local proxy for Gemini API (development) — PHP

This small PHP proxy forwards requests from the browser to the Google Generative Language API so you don't expose your API key in client-side code.

## Setup (Windows PowerShell)

1. Make sure PHP is installed and on your PATH. You can download PHP for Windows from https://windows.php.net/ or use WSL.

2. Set your Google API key in the environment (PowerShell):

```powershell
$env:GOOGLE_API_KEY = "YOUR_API_KEY_HERE"
```

3. Start the built-in PHP server from the project folder (serves current directory on port 8000):

```powershell
# from d:\Projects\python_project
php -S 0.0.0.0:8000
```

4. The PHP proxy endpoint is `http://localhost:8000/api.php`. It accepts POST JSON `{ "text": "..." }` and returns `{ "text": "..." }` on success.

## Frontend

- `index.html` has been updated to POST to `/api.php`. Open the page in a browser while the PHP server is running.

## Security notes

- This proxy is for local development only. For production:
  - Run behind HTTPS and a proper web server.
  - Protect the endpoint with authentication and rate-limiting.
  - Store the API key in a secure secret store (Key Vault, Secrets Manager).
  - Do not commit your API key to source control.
