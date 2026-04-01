---
inclusion: auto
---

# Deploy Branches Configuration

When pushing changes to the deploy repositories, always use these branches:

## Frontend
- Remote: `deploy` → `https://github.com/Strega-ABVecsa-Developments/vecsa-frontend.git`
- Branch: `sandboxRailwayGrupoVecsaFrontend`
- Command: `git push deploy $(git subtree split --prefix=vecsa-frontend):refs/heads/sandboxRailwayGrupoVecsaFrontend --force`

## Backend
- Remote: `backend-deploy` → `https://github.com/Strega-ABVecsa-Developments/vecsa-backend.git`
- Branch: `sandboxRailwayGrupoVecsaBackend`
- Command: `git push backend-deploy $(git subtree split --prefix=vecsa-backend):refs/heads/sandboxRailwayGrupoVecsaBackend --force`

## Workflow
1. Commit all changes in the monorepo root
2. Push frontend subtree to deploy remote
3. Push backend subtree to backend-deploy remote
