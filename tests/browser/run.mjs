import fs from 'node:fs';
import { randomBytes } from 'node:crypto';
import { spawnSync } from 'node:child_process';
import path from 'node:path';

const database = path.resolve('database/e2e.sqlite');
const password = randomBytes(24).toString('base64url');
const env = {
    ...process.env,
    APP_ENV: 'e2e',
    E2E_ADMIN_PASSWORD: password,
    DB_CONNECTION: 'sqlite',
    DB_DATABASE: database,
    APP_URL: 'http://127.0.0.1:8011',
    INERTIA_SSR_THROW_ON_ERROR: 'true',
};
fs.writeFileSync(database, '');
const key = 'base64:' + randomBytes(32).toString('base64');
fs.writeFileSync(
    '.env.e2e',
    `APP_NAME=Portofolio\nAPP_ENV=e2e\nAPP_KEY=${key}\nAPP_DEBUG=false\nAPP_URL=http://127.0.0.1:8011\nDB_CONNECTION=sqlite\nDB_DATABASE="${database.replaceAll('\\', '/')}"\nSESSION_DRIVER=database\nCACHE_STORE=database\nQUEUE_CONNECTION=database\nMAIL_MAILER=log\nINERTIA_SSR_ENABLED=true\nINERTIA_SSR_THROW_ON_ERROR=true\n`,
);
for (const args of [
    ['artisan', 'migrate', '--env=e2e', '--force', '--no-interaction'],
    ['artisan', 'db:seed', '--class=BrowserSeeder', '--env=e2e', '--force', '--no-interaction'],
]) {
    const result = spawnSync('php', args, { env, stdio: 'inherit' });
    if (result.status !== 0) process.exit(result.status ?? 1);
}
const result = spawnSync(
    process.execPath,
    ['node_modules/@playwright/test/cli.js', 'test', ...process.argv.slice(2)],
    { env, stdio: 'inherit' },
);
process.exit(result.status ?? 1);
