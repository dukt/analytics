const {test, expect} = require('@playwright/test');

test('Shoud show Analytics widgets in the Dashboard’s new widget menu', async ({ page, context, baseURL }) => {
  await page.goto(baseURL + '/dashboard');
  const title = page.locator('h1');
  await expect(title).toHaveText('Dashboard');

  const handle = await page.$('#newwidgetmenubtn');
  await handle.click()
  await page.waitForSelector('#new-widget-menu ul')

  expect((await page.locator('#new-widget-menu ul li a[data-type="dukt\\\\analytics\\\\widgets\\\\Realtime"]:has-text("Active users")').count() > 0)).toBeTruthy();
  expect((await page.locator('#new-widget-menu ul li a[data-type="dukt\\\\analytics\\\\widgets\\\\Report"]:has-text("Analytics Report")').count() > 0)).toBeTruthy();
  expect((await page.locator('#new-widget-menu ul li a[data-type="dukt\\\\analytics\\\\widgets\\\\Ecommerce"]:has-text("E-commerce")').count() > 0)).toBeTruthy();
});
