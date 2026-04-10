#!/usr/bin/env bash
# Publica los subtrees vecsa-frontend y vecsa-backend SIEMPRE a las ramas sandbox
# de los remotes deploy y backend-deploy (Railway sandbox). Ver .kiro/steering/deploy-branches.md
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "→ vecsa-frontend → deploy/sandboxRailwayGrupoVecsaFrontend"
git push deploy "$(git subtree split --prefix=vecsa-frontend):refs/heads/sandboxRailwayGrupoVecsaFrontend" --force

echo "→ vecsa-backend → backend-deploy/sandboxRailwayGrupoVecsaBackend"
git push backend-deploy "$(git subtree split --prefix=vecsa-backend):refs/heads/sandboxRailwayGrupoVecsaBackend" --force

echo "Listo."
