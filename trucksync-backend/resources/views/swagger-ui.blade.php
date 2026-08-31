<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'TruckSync') }} API Documentation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
    <style>
        body {
            margin: 0;
        }

        .swagger-ui .topbar {
            display: none;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>

    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.addEventListener('load', () => {
            window.ui = SwaggerUIBundle({
                url: @json(route('swagger.openapi')),
                dom_id: '#swagger-ui',
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset,
                ],
                layout: 'BaseLayout',
                persistAuthorization: true,
                deepLinking: true,
                displayRequestDuration: true,
            });
        });
    </script>
</body>
</html>
