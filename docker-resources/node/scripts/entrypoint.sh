#!/bin/sh

# Copy original vite.config.js to Docker-specific version
mkdir -p /app/.docker
cp /app/vite.config.js /app/.docker/vite.config.docker.js

# Update resource paths to absolute paths
sed -i "s|'resources/css/app.css'|'/app/resources/css/app.css'|g" /app/.docker/vite.config.docker.js
sed -i "s|'resources/js/app.js'|'/app/resources/js/app.js'|g" /app/.docker/vite.config.docker.js

# Add Docker-specific server configuration if not already present
if ! grep -q "server:" /app/.docker/vite.config.docker.js; then
    # Insert server config before the closing }); of defineConfig
    sed -i '/^});$/i\    server: {\n        host: '\''0.0.0.0'\'',\n        port: 5173,\n        hmr: {\n            host: '\''localhost'\'',\n        },\n    },' /app/.docker/vite.config.docker.js
fi

# Install dependencies and start Vite with Docker config
npm install && npm run dev -- --config .docker/vite.config.docker.js
