---
inclusion: auto
---

# Deploy Branches Configuration

## Policy (mandatory)

For normal development, **always** push subtree updates to the **sandbox** branches below—both frontend and backend. Do **not** push routine work to `main`, `grupovecsa*Railway`, `vecsa*New`, or `*AmericaV` on `deploy` / `backend-deploy` unless there is an explicit release or merge agreement.

**Do not use `git push origin main` (or any `git push` to `origin`) as the sandbox deploy step.** Sandbox goes only through `deploy` / `backend-deploy` subtree pushes below.

Commits happen in this monorepo (typically on `main`); the split push targets only these sandbox branch names.

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
1. Commit all changes in the monorepo root (`git add` / `git commit`).
2. Push frontend subtree to `deploy` → `sandboxRailwayGrupoVecsaFrontend`.
3. Push backend subtree to `backend-deploy` → `sandboxRailwayGrupoVecsaBackend`.

Pushing to `origin` (monorepo) is **out of scope** for this sandbox workflow unless someone explicitly asks for it (release / backup); routine sandbox updates stop after step 3.

Shortcut (both pushes in order):

```bash
./tools/push-sandbox-subtrees.sh
```
