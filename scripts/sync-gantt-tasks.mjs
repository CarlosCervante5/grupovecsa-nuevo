#!/usr/bin/env node
/**
 * Sincroniza tareas del Gantt (API Railway) → .gantt/TAREAS.md para Cursor IDE.
 * Env: GANTT_API_URL, GANTT_GITHUB_USER, opcional GANTT_SYNC_TOKEN, GANTT_REPO_NAME
 * Archivo opcional en la raíz: `.env.gantt` (ver `.env.gantt.example`)
 */
import { execSync } from 'node:child_process';
import fs from 'node:fs';
import http from 'node:http';
import https from 'node:https';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(__dirname, '..');

/**
 * Carga variables desde `.env.gantt` en la raíz del repo (opcional, no versionar).
 */
function loadLocalGanttEnv() {
  const filePath = path.join(repoRoot, '.env.gantt');
  if (!fs.existsSync(filePath)) return;
  const lines = fs.readFileSync(filePath, 'utf8').split(/\r?\n/);
  for (const line of lines) {
    const t = line.trim();
    if (!t || t.startsWith('#')) continue;
    const eq = t.indexOf('=');
    if (eq <= 0) continue;
    const key = t.slice(0, eq).trim();
    let val = t.slice(eq + 1).trim();
    if (
      (val.startsWith('"') && val.endsWith('"')) ||
      (val.startsWith("'") && val.endsWith("'"))
    ) {
      val = val.slice(1, -1);
    }
    if (!(key in process.env) || process.env[key] === '') {
      process.env[key] = val;
    }
  }
}

loadLocalGanttEnv();

function env(name, fallback = '') {
  const v = process.env[name];
  return v != null && String(v).trim() !== '' ? String(v).trim() : fallback;
}

function detectRepoName() {
  const override = env('GANTT_REPO_NAME');
  if (override) return override;
  try {
    const url = execSync('git remote get-url origin', {
      cwd: repoRoot,
      encoding: 'utf8',
    }).trim();
    const m = url.match(/\/([^/]+?)(?:\.git)?$/);
    if (m) return m[1];
  } catch {
    /* sin git */
  }
  return path.basename(repoRoot);
}

function formatDate(iso) {
  if (!iso) return '—';
  try {
    return new Date(iso).toISOString().slice(0, 10);
  } catch {
    return iso;
  }
}

function httpGetJson(url, headers = {}) {
  return new Promise((resolve, reject) => {
    const lib = url.protocol === 'https:' ? https : http;
    const req = lib.get(
      url,
      { headers: { Accept: 'application/json', ...headers } },
      (res) => {
        let body = '';
        res.on('data', (c) => (body += c));
        res.on('end', () => {
          if (res.statusCode && res.statusCode >= 400) {
            reject(new Error(`API ${res.statusCode}: ${body.slice(0, 500)}`));
            return;
          }
          try {
            resolve(JSON.parse(body));
          } catch (e) {
            reject(new Error(`JSON inválido: ${e.message}`));
          }
        });
      },
    );
    req.on('error', reject);
  });
}

async function fetchTasks(apiBase, assignee, token) {
  const base = apiBase.replace(/\/$/, '');
  const url = new URL(`${base}/api/cursor/tasks`);
  url.searchParams.set('assignee', assignee);
  if (token) url.searchParams.set('token', token);

  const headers = {};
  if (token) headers['X-Gantt-Sync-Token'] = token;

  return httpGetJson(url, headers);
}

function buildMarkdown({ tasks, repoName, assignee, syncedAt, convention }) {
  const lines = [
    '# Tareas Gantt (Cursor IDE)',
    '',
    `> Sincronizado: ${syncedAt || new Date().toISOString()}`,
    `> Repositorio: **${repoName}** · Asignado: **@${assignee}**`,
    '',
    '## Convención de commits',
    '',
    '| Acción | Mensaje |',
    '|--------|---------|',
    '| Avance (+10% por commit con `#N`) | `descripción #N` |',
    '| Terminada (100%, en rama producción) | `gantt:done #N: descripción` |',
    '',
  ];

  if (convention?.incrementPercent) {
    lines.push(
      `El avance del proyecto sube **${convention.incrementPercent}%** por commit nuevo que mencione el issue. Solo al **100%** con \`gantt:done\` en producción.`,
      '',
    );
  }

  if (!tasks.length) {
    lines.push('_No hay tareas asignadas para este repositorio en el Gantt._', '');
    return lines.join('\n');
  }

  for (const t of tasks) {
    const issue = t.issueNumber ? `#${t.issueNumber}` : '(sin issue)';
    lines.push(`## ${issue} — ${t.title}`, '');
    if (t.description) lines.push(t.description, '');
    lines.push(
      `- **Progreso:** ${t.progress ?? 0}%`,
      `- **Proyecto:** ${t.project?.name ?? '—'} (\`${t.project?.repoFullName ?? '—'}\`)`,
      `- **Fechas:** ${formatDate(t.startDate)} → ${formatDate(t.endDate)}`,
      `- **Commit avance:** \`${t.commitProgressTemplate ?? '{summary} #N'}\``,
      `- **Commit terminado:** \`${t.commitDoneTemplate ?? 'gantt:done #N: {summary}'}\``,
    );
    if (t.conventionHint) lines.push(`- ${t.conventionHint}`);
    lines.push('');
  }

  lines.push('---', '', '### Commit desde terminal', '', '```bash');
  lines.push(
    'git commit -m "$(node scripts/gantt-commit-msg.mjs --summary \'descripción\' --issue N)"',
  );
  lines.push('```', '');
  lines.push('Tarea activa (opcional): `echo \'{"issueNumber":N,"title":"..."}\' > .gantt/active-task.json`', '');

  return lines.join('\n');
}

async function main() {
  const apiUrl = env('GANTT_API_URL');
  const assignee = env('GANTT_GITHUB_USER', env('GITHUB_USER', '')).toLowerCase();
  const token = env('GANTT_SYNC_TOKEN');

  if (!apiUrl) {
    console.error('Falta GANTT_API_URL (ej. https://awake-wonder-production.up.railway.app)');
    process.exit(1);
  }
  if (!assignee) {
    console.error('Falta GANTT_GITHUB_USER (usuario GitHub / Gantt)');
    process.exit(1);
  }

  const repoName = detectRepoName();
  console.log(`Sync Gantt → ${repoName} (@${assignee})`);

  const data = await fetchTasks(apiUrl, assignee, token);
  const allTasks = Array.isArray(data.tasks) ? data.tasks : [];
  const tasks = allTasks.filter((t) => {
    const name = t.project?.repoName || '';
    return name === repoName;
  });

  const ganttDir = path.join(repoRoot, '.gantt');
  fs.mkdirSync(ganttDir, { recursive: true });

  const md = buildMarkdown({
    tasks,
    repoName,
    assignee,
    syncedAt: data.syncedAt,
    convention: data.commitConvention,
  });

  const mdPath = path.join(ganttDir, 'TAREAS.md');
  fs.writeFileSync(mdPath, md, 'utf8');

  const metaPath = path.join(ganttDir, 'sync-meta.json');
  fs.writeFileSync(
    metaPath,
    JSON.stringify(
      {
        syncedAt: data.syncedAt ?? new Date().toISOString(),
        assignee,
        repoName,
        tasks,
        allTasksCount: allTasks.length,
        commitConvention: data.commitConvention ?? null,
      },
      null,
      2,
    ),
    'utf8',
  );

  console.log(`✓ ${tasks.length} tarea(s) para ${repoName} → .gantt/TAREAS.md`);
  if (allTasks.length > tasks.length) {
    console.log(`  (${allTasks.length - tasks.length} tarea(s) de otros repos omitidas)`);
  }
}

main().catch((err) => {
  console.error(err.message || err);
  process.exit(1);
});
