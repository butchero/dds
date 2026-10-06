---
paths:
  - '**/*.{php,blade.php}'
---

# Phpblade Php

## Update the project overview when behavior changes
When a change adds a feature or changes behavior described in .ai/guidelines/dds.md, update that file in the same change. Keep it a short overview, not an implementation log. Then refresh AGENTS.md with `/usr/bin/php artisan boost:install --guidelines --no-interaction`.
