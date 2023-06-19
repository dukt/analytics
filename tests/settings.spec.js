const {test, expect} = require('@playwright/test');

test('Shoud show the Settings page', async ({ page, context, baseURL }) => {
  await page.goto(baseURL + '/analytics/settings');
  const title = page.locator('h1');
  await expect(title).toHaveText('Analytics');
});

test('Shoud show the OAuth Settings page', async ({ page, context, baseURL }) => {
  await page.goto(baseURL + '/analytics/settings/oauth');
  const title = page.locator('h1');
  await expect(title).toHaveText('OAuth Settings');
});

test('Shoud show the Views Settings page', async ({ page, context, baseURL }) => {
  await page.goto(baseURL + '/analytics/settings/views');
  const title = page.locator('h1');
  await expect(title).toHaveText('Analytics');
});

test('Shoud show the Sites Settings page', async ({ page, context, baseURL }) => {
  await page.goto(baseURL + '/analytics/settings/sites');
  const title = page.locator('h1');
  await expect(title).toHaveText('Analytics');
});