#!/bin/sh
# Container entrypoint: write .env from the platform's environment, then serve public/.
set -e
BASE="${APP_BASE_URL:-${RENDER_EXTERNAL_URL:-http://localhost:${PORT:-10000}}}"
cat > .env <<EOF
CI_ENVIRONMENT = ${CI_ENVIRONMENT:-production}
app.baseURL = '${BASE%/}/'
EOF
exec php -S 0.0.0.0:"${PORT:-10000}" -t public /app/vendor/codeigniter4/framework/system/rewrite.php
