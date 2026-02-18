import fs from 'fs';
import { test } from '@playwright/test';

// Ensure screenshots directory exists
fs.mkdirSync('screenshots', { recursive: true });

test('visual snapshots', async ({ page }) => {
  await page.goto('/');
  await page.waitForLoadState('networkidle');
  await page.screenshot({ path: 'screenshots/home.png', fullPage: true });

  await page.goto('/login');
  await page.waitForLoadState('networkidle');
  await page.screenshot({ path: 'screenshots/login.png', fullPage: true });

  await page.goto('/register');
  await page.waitForLoadState('networkidle');
  await page.screenshot({ path: 'screenshots/register.png', fullPage: true });
});
