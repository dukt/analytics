const {test, expect} = require('@playwright/test');

test('Shoud show an Analytics report field', async ({ page, context, baseURL }) => {
  await page.goto(baseURL + '/entries/plugins/2-analytics');
  const title = page.locator('h1');
  await expect(title).toHaveText('Analytics');

  const label = page.locator('div[data-type="dukt\\\\analytics\\\\fields\\\\Report"] div.heading label');
  await expect(label).toHaveText('Analytics Report');
});
