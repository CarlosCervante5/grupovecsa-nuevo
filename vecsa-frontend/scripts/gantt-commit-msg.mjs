#!/usr/bin/env node
/**
 * Genera mensaje de commit según convención Gantt.
 * Uso: node scripts/gantt-commit-msg.mjs --summary "texto" --issue 42 [--done]
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(__dirname, '..');

function parseArgs(argv) {
  const out = { summary: '', issue: null, done: false };
  for (let i = 2; i < argv.length; i++) {
    const a = argv[i];
    if (a === '--done') out.done = true;
    else if (a === '--summary' && argv[i + 1]) out.summary = argv[++i];
    else if (a === '--issue' && argv[i + 1]) out.issue = Number(argv[++i]);
  }
  return out;
}

function loadActiveTask() {
  const p = path.join(repoRoot, '.gantt', 'active-task.json');
  if (!fs.existsSync(p)) return null;
  try {
    return JSON.parse(fs.readFileSync(p, 'utf8'));
  } catch {
    return null;
  }
}

function main() {
  let { summary, issue, done } = parseArgs(process.argv);
  const active = loadActiveTask();

  if (active?.issueNumber && (issue == null || Number.isNaN(issue))) {
    issue = Number(active.issueNumber);
  }
  if (!summary && active?.title) summary = active.title;

  if (!summary?.trim()) {
    console.error('Falta --summary o .gantt/active-task.json con title');
    process.exit(1);
  }
  if (issue == null || Number.isNaN(issue)) {
    console.error('Falta --issue N o active-task.json con issueNumber');
    process.exit(1);
  }

  summary = summary.trim();
  const msg = done
    ? `gantt:done #${issue}: ${summary}`
    : `${summary} #${issue}`;

  process.stdout.write(msg);
}

main();
